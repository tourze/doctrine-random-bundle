<?php

namespace Tourze\DoctrineRandomBundle\Tests\EventSubscriber;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;
use Tourze\DoctrineRandomBundle\EventSubscriber\RandomStringListener;

/**
 * 测试 RandomStringListener 事件订阅器
 */
class RandomStringListenerTest extends TestCase
{
    private RandomStringListener $listener;

    /** @var MockObject|PropertyAccessor */
    private $propertyAccessor;

    /** @var MockObject|LoggerInterface */
    private $logger;

    protected function setUp(): void
    {
        $this->propertyAccessor = $this->createMock(PropertyAccessor::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new RandomStringListener(
            $this->propertyAccessor,
            $this->logger
        );
    }

    /**
     * 测试 generateRandomString 方法
     */
    public function testGenerateRandomString(): void
    {
        $reflection = new \ReflectionClass(RandomStringListener::class);
        $method = $reflection->getMethod('generateRandomString');
        $method->setAccessible(true);

        $result = $method->invoke($this->listener, 10);
        $this->assertSame(10, strlen($result));

        // 测试不同长度
        $result2 = $method->invoke($this->listener, 20);
        $this->assertSame(20, strlen($result2));

        // 确保随机性
        $result3 = $method->invoke($this->listener, 10);
        $this->assertNotSame($result, $result3);
    }

    /**
     * 测试实体没有 RandomStringColumn 属性时
     */
    public function testPrePersistWithoutRandomStringColumn(): void
    {
        $entity = new class {
            private string $id = '';
        };

        $reflection = new \ReflectionClass($entity);

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('getReflectionClass')->willReturn($reflection);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')->willReturn($classMetadata);

        $this->propertyAccessor->method('isWritable')->willReturn(true);

        // 直接调用 prePersistEntity 方法而不是通过 PrePersistEventArgs
        $this->listener->prePersistEntity($objectManager, $entity);

        // 没有属性标记，不应该设置任何值
        $this->assertSame('', $reflection->getProperty('id')->getValue($entity));
    }

    /**
     * 提供一个标记了 RandomStringColumn 属性的测试实体
     */
    private function getEntityWithRandomStringColumn(): object
    {
        return new class {
            #[RandomStringColumn(prefix: 'test_', length: 10)]
            private string $randomId = '';

            public function getRandomId(): string
            {
                return $this->randomId;
            }

            public function setRandomId(string $randomId): self
            {
                $this->randomId = $randomId;
                return $this;
            }
        };
    }

    /**
     * 测试 prePersistEntity 方法处理带有 RandomStringColumn 属性的实体
     */
    public function testPrePersistWithRandomStringColumn(): void
    {
        $entity = $this->getEntityWithRandomStringColumn();
        $reflection = new \ReflectionClass($entity);

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('getReflectionClass')->willReturn($reflection);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')->willReturn($classMetadata);

        $this->propertyAccessor->method('isWritable')->willReturn(true);

        // 模拟 PropertyAccessor 设置值的行为
        $this->propertyAccessor->expects($this->once())
            ->method('setValue')
            ->with(
                $this->identicalTo($entity),
                $this->equalTo('randomId'),
                $this->callback(function ($value) {
                    return is_string($value)
                        && strlen($value) === 10
                        && strpos($value, 'test_') === 0;
                })
            )
            ->willReturnCallback(function ($entity, $property, $value) {
                $entity->setRandomId($value);
            });

        // 直接调用 prePersistEntity 方法
        $this->listener->prePersistEntity($objectManager, $entity);

        // 验证值被正确设置
        $randomId = $entity->getRandomId();
        $this->assertStringStartsWith('test_', $randomId);
        $this->assertSame(10, strlen($randomId));
    }

    /**
     * 测试当属性已有值时不进行覆盖
     */
    public function testPrePersistDoesNotOverrideExistingValue(): void
    {
        $entity = $this->getEntityWithRandomStringColumn();
        $entity->setRandomId('existing_value');

        $reflection = new \ReflectionClass($entity);

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('getReflectionClass')->willReturn($reflection);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')->willReturn($classMetadata);

        $this->propertyAccessor->method('isWritable')->willReturn(true);

        // 确保 setValue 不会被调用，因为已经有值了
        $this->propertyAccessor->expects($this->never())->method('setValue');

        // 直接调用 prePersistEntity 方法
        $this->listener->prePersistEntity($objectManager, $entity);

        // 验证现有值不会被覆盖
        $this->assertSame('existing_value', $entity->getRandomId());
    }
}
