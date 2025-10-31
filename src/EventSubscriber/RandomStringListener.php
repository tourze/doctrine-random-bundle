<?php

declare(strict_types=1);

namespace Tourze\DoctrineRandomBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tourze\DoctrineEntityCheckerBundle\Checker\EntityCheckerInterface;
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;

/**
 * 在保存实体时，自动分配随机值
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'doctrine_random')]
readonly class RandomStringListener implements EntityCheckerInterface
{
    public function __construct(
        #[Autowire(service: 'doctrine-random.property-accessor')] private PropertyAccessor $propertyAccessor,
        private LoggerInterface $logger,
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
            $this->processPropertyRandomString($entity, $property);
        }
    }

    private function processPropertyRandomString(object $entity, \ReflectionProperty $property): void
    {
        if (!$this->propertyAccessor->isWritable($entity, $property->getName())) {
            return;
        }

        foreach ($property->getAttributes(RandomStringColumn::class) as $attribute) {
            $attributeInstance = $attribute->newInstance();
            if ($this->shouldSkipProperty($entity, $property)) {
                continue;
            }

            $randomValue = $this->buildRandomValue($attributeInstance);
            $this->setRandomValue($entity, $property, $randomValue);
        }
    }

    private function shouldSkipProperty(object $entity, \ReflectionProperty $property): bool
    {
        try {
            $value = $property->getValue($entity);

            return null !== $value && '' !== $value;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function buildRandomValue(RandomStringColumn $attribute): string
    {
        $randomValue = $this->generateRandomString($attribute->length);

        if ('' !== $attribute->prefix) {
            $randomValue = "{$attribute->prefix}{$randomValue}";
        }

        if ($attribute->length > 0) {
            $randomValue = substr($randomValue, 0, $attribute->length);
        }

        return $randomValue;
    }

    private function setRandomValue(object $entity, \ReflectionProperty $property, string $value): void
    {
        $this->logger->debug("为{$property->getName()}分配随机字符串", [
            'id' => $value,
            'entity' => $entity,
        ]);
        $this->propertyAccessor->setValue($entity, $property->getName(), $value);
    }

    public function preUpdateEntity(ObjectManager $objectManager, object $entity, PreUpdateEventArgs $eventArgs): void
    {
        // 更新时，我们不特地去处理，因为有可能我们是需要去数据库特地清空，故意不给值的
    }
}
