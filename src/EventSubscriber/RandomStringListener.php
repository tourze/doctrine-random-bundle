<?php

namespace Tourze\DoctrineRandomBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tourze\DoctrineEntityCheckerBundle\Checker\EntityCheckerInterface;
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;

/**
 * 在保存实体时，自动分配随机值
 */
#[AsDoctrineListener(event: Events::prePersist)]
class RandomStringListener implements EntityCheckerInterface
{
    public function __construct(
        #[Autowire(service: 'doctrine-random.property-accessor')] private readonly PropertyAccessor $propertyAccessor,
        private readonly LoggerInterface $logger,
    ) {
    }

    private function generateRandomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->prePersistEntity($args->getObjectManager(), $args->getObject());
    }

    public function prePersistEntity(ObjectManager $objectManager, object $entity): void
    {
        $reflection = $objectManager->getClassMetadata($entity::class)->getReflectionClass();
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
            // 如果字段不可以写入，直接跳过即可
            if (!$this->propertyAccessor->isWritable($entity, $property->getName())) {
                continue;
            }

            foreach ($property->getAttributes(RandomStringColumn::class) as $attribute) {
                $attribute = $attribute->newInstance();
                /* @var RandomStringColumn $attribute */

                try {
                    // 已经有值了，我们就跳过
                    $v = $property->getValue($entity);
                    if (!empty($v)) {
                        continue;
                    }
                } catch (\Throwable $exception) {
                    // 忽略
                }

                $idValue = $this->generateRandomString($attribute->length);
                if ($attribute->prefix !== null && $attribute->prefix !== '') {
                    $idValue = "{$attribute->prefix}{$idValue}";
                }

                if ($attribute->length > 0) {
                    $idValue = substr($idValue, 0, $attribute->length);
                }

                $this->logger->debug("为{$property->getName()}分配随机字符串", [
                    'id' => $idValue,
                    'entity' => $entity,
                ]);
                $this->propertyAccessor->setValue($entity, $property->getName(), $idValue);
            }
        }
    }

    public function preUpdateEntity(ObjectManager $objectManager, object $entity, PreUpdateEventArgs $eventArgs): void
    {
        // 更新时，我们不特地去处理，因为有可能我们是需要去数据库特地清空，故意不给值的
    }
}
