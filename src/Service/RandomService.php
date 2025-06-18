<?php

namespace Tourze\DoctrineRandomBundle\Service;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RandomService
{
    public function __construct(
        private readonly LockFactory $lockFactory,
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache,
    )
    {
    }

    /**
     * 传入一个QueryBuilder，我们返回一个随机结果
     */
    public function getRandomResult(QueryBuilder $queryBuilder, int $limit = 1, $rangeSize = 1000): \Traversable
    {
        // 假设主键叫 id
        $pkName = 'id';
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $dql = $queryBuilder->getDQL();
        $hash = 'getRandomResult' . md5(serialize([$dql, $queryBuilder->getMaxResults(), $queryBuilder->getFirstResult(), $rangeSize]));

        // 先读取所有可能的ID
        $allIds = $this->cache->get($hash, function (ItemInterface $item) use ($queryBuilder, $rootAlias, $pkName, $rangeSize) {
            $qb = clone $queryBuilder;
            $qb->select("{$rootAlias}.{$pkName}");

            // 考虑到，外部进入的请求可能是 SELECT u FROM Users u 这种范围比较大的数据，我们在这里再处理一次 maxResult，以减少需要缓存的数据量
            $maxResult = $qb->getMaxResults();
            if (null === $maxResult || 0 === $maxResult) {
                $maxResult = $rangeSize;
            }
            if ($maxResult > $rangeSize) {
                $maxResult = $rangeSize;
            }
            $qb->setMaxResults($maxResult);

            $item->expiresAfter(60); // 每个列表，我们只缓存1分钟

            return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
        });
        shuffle($allIds);

        foreach ($allIds as $pickId) {
            // 在这里，我们对每次可能被取出的数，做一次锁，防止多人拿到同一个ID
            // 但是这样子就有一个新问题，这个函数的整体性能会受限于上面的 rangeSize
            $lockKey = "{$hash}_{$pickId}";
            $lock = $this->lockFactory->createLock($lockKey);
            try {
                // 如果有人在用了，那我们就跳过继续下一个
                if (!$lock->acquire()) {
                    continue;
                }
            } catch (\Throwable $exception) {
                $this->logger->error('获取随机记录时，锁定失败', [
                    'dql' => $dql,
                    'limit' => $limit,
                    'exception' => $exception,
                ]);
                continue;
            }

            try {
                $qb = clone $queryBuilder;
                $qb->andWhere("{$rootAlias}.{$pkName} = :getRandomResultPickId");
                $qb->setParameter('getRandomResultPickId', $pickId);
                $qb->resetDQLPart('groupBy');
                $qb->resetDQLPart('having');
                $qb->resetDQLPart('orderBy');
                $row = $qb->getQuery()->getOneOrNullResult();
                if ($row !== null) {
                    yield $row;
                    --$limit;
                }
            } finally {
                $lock->release();
            }

            if ($limit <= 0) {
                break;
            }
        }
    }
}
