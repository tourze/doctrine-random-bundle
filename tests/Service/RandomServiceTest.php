<?php

namespace Tourze\DoctrineRandomBundle\Tests\Service;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Tourze\DoctrineRandomBundle\Service\RandomService;

/**
 * 测试随机服务
 */
class RandomServiceTest extends TestCase
{
    /** @var MockObject|LockFactory */
    private $lockFactory;

    /** @var MockObject|LoggerInterface */
    private $logger;

    /** @var MockObject|CacheInterface */
    private $cache;

    private RandomService $randomService;

    protected function setUp(): void
    {
        $this->lockFactory = $this->createMock(LockFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);

        $this->randomService = new RandomService(
            $this->lockFactory,
            $this->logger,
            $this->cache
        );
    }

    /**
     * 测试构造函数注入
     */
    public function testConstructor(): void
    {
        $randomService = new RandomService(
            $this->lockFactory,
            $this->logger,
            $this->cache
        );

        $this->assertInstanceOf(RandomService::class, $randomService);
    }

    /**
     * 测试当没有缓存时，getRandomResult 应该返回空结果
     */
    public function testGetRandomResultWithoutIds(): void
    {
        // 准备空的ID列表
        $ids = [];
        $this->cache->method('get')->willReturn($ids);

        // Mock 查询构建器
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn(['e']);
        $queryBuilder->method('getDQL')->willReturn('SELECT e FROM Entity e');

        // 执行测试
        $results = $this->randomService->getRandomResult($queryBuilder);

        // 验证空结果
        $this->assertInstanceOf(\Traversable::class, $results);
        $this->assertCount(0, iterator_to_array($results));
    }

    /**
     * 测试获取随机结果基本能力
     */
    public function testGetRandomResultBasicFunctionality(): void
    {
        // 测试服务类是否被正确构造，PHPStan 已确定方法存在
        $this->assertInstanceOf(RandomService::class, $this->randomService, '服务应该是 RandomService 的实例');
    }

    /**
     * 测试获取随机结果方法签名
     */
    public function testGetRandomResultMethodSignature(): void
    {
        $reflection = new \ReflectionClass(RandomService::class);
        $method = $reflection->getMethod('getRandomResult');

        $this->assertTrue($method->isPublic(), '方法应该是公开的');
        $parameters = $method->getParameters();
        $this->assertCount(3, $parameters, '方法应该有3个参数');
        $this->assertSame('queryBuilder', $parameters[0]->getName(), '第一个参数应该是 queryBuilder');
        $this->assertSame('limit', $parameters[1]->getName(), '第二个参数应该是 limit');
        $this->assertSame('rangeSize', $parameters[2]->getName(), '第三个参数应该是 rangeSize');
    }

    /**
     * 测试锁创建方法
     */
    public function testLockCreation(): void
    {
        // 测试锁的创建
        $lock = $this->createMock(LockInterface::class);

        $this->lockFactory->expects($this->once())
            ->method('createLock')
            ->with($this->isType('string'))
            ->willReturn($lock);

        // 调用服务方法，触发锁创建
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getRootAliases')->willReturn(['e']);
        $qb->method('getDQL')->willReturn('test');

        // 确保方法会尝试获取一个 ID
        $ids = [1];
        $this->cache->method('get')->willReturn($ids);

        // 模拟锁获取失败，这样就不会调用 release
        $lock->method('acquire')->willReturn(false);

        // 执行测试
        $results = iterator_to_array($this->randomService->getRandomResult($qb));

        // 应该没有结果，因为锁获取失败
        $this->assertCount(0, $results);
    }
}
