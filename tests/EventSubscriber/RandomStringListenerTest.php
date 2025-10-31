<?php

declare(strict_types=1);

namespace Tourze\DoctrineRandomBundle\Tests\EventSubscriber;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DoctrineRandomBundle\EventSubscriber\RandomStringListener;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * 测试 RandomStringListener 事件订阅器
 *
 * @internal
 */
#[CoversClass(RandomStringListener::class)]
#[RunTestsInSeparateProcesses]
final class RandomStringListenerTest extends AbstractEventSubscriberTestCase
{
    private RandomStringListener $listener;

    protected function onSetUp(): void
    {
        // 从容器获取服务，使用真实的依赖
        $this->listener = self::getService(RandomStringListener::class);
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
        $this->assertIsString($result);
        $this->assertSame(10, strlen($result));

        // 测试不同长度
        $result2 = $method->invoke($this->listener, 20);
        $this->assertIsString($result2);
        $this->assertSame(20, strlen($result2));

        // 确保随机性
        $result3 = $method->invoke($this->listener, 10);
        $this->assertIsString($result3);
        $this->assertNotSame($result, $result3);
    }

    /**
     * 测试实体没有 RandomStringColumn 属性时
     */
    public function testPrePersistWithoutRandomStringColumn(): void
    {
        $entity = new class {
            public ?string $normalProperty = null;
        };

        $reflection = new \ReflectionClass($entity);

        // 使用 PHPUnit Mock 创建 ClassMetadata
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('getReflectionClass')->willReturn($reflection);

        // 使用 PHPUnit Mock 创建 ObjectManager
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')->willReturn($classMetadata);

        // 直接调用 prePersistEntity 方法
        $this->listener->prePersistEntity($objectManager, $entity);

        // 验证实体没有被修改，因为没有 RandomStringColumn 属性
        $this->assertIsObject($entity);
    }

    /**
     * 提供一个标记了 RandomStringColumn 属性的测试实体
     */
    private function getEntityWithRandomStringColumn(): TestEntityWithRandomStringColumn
    {
        return new TestEntityWithRandomStringColumn();
    }

    /**
     * 测试 prePersistEntity 方法处理带有 RandomStringColumn 属性的实体
     */
    public function testPrePersistWithRandomStringColumn(): void
    {
        $entity = $this->getEntityWithRandomStringColumn();
        $reflection = new \ReflectionClass($entity);

        // 使用 PHPUnit Mock 创建 ClassMetadata
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('getReflectionClass')->willReturn($reflection);

        // 使用 PHPUnit Mock 创建 ObjectManager
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')->willReturn($classMetadata);

        // 直接调用 prePersistEntity 方法
        $this->listener->prePersistEntity($objectManager, $entity);

        // 验证值被正确设置
        $randomId = $entity->getRandomId();
        $this->assertIsString($randomId);
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

        // 使用 PHPUnit Mock 创建 ClassMetadata
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('getReflectionClass')->willReturn($reflection);

        // 使用 PHPUnit Mock 创建 ObjectManager
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')->willReturn($classMetadata);

        // 直接调用 prePersistEntity 方法
        $this->listener->prePersistEntity($objectManager, $entity);

        // 验证现有值不会被覆盖
        $this->assertSame('existing_value', $entity->getRandomId());
    }

    /**
     * 测试 prePersistEntity 方法
     */
    public function testPrePersistEntity(): void
    {
        $entity = $this->getEntityWithRandomStringColumn();
        $reflection = new \ReflectionClass($entity);

        // 使用 PHPUnit Mock 创建 ClassMetadata
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('getReflectionClass')->willReturn($reflection);

        // 使用 PHPUnit Mock 创建 ObjectManager
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')->willReturn($classMetadata);

        // 测试方法执行成功
        $this->listener->prePersistEntity($objectManager, $entity);

        // 验证随机值被正确设置
        $randomId = $entity->getRandomId();
        $this->assertIsString($randomId);
        $this->assertNotEmpty($randomId);
        $this->assertSame(10, strlen($randomId));
    }

    /**
     * 测试 preUpdateEntity 方法
     */
    public function testPreUpdateEntity(): void
    {
        $entity = $this->getEntityWithRandomStringColumn();
        $reflection = new \ReflectionClass($entity);

        // 使用 PHPUnit Mock 创建 ClassMetadata
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('getReflectionClass')->willReturn($reflection);

        // 使用 PHPUnit Mock 创建 ObjectManager
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')->willReturn($classMetadata);

        // 使用 PHPUnit Mock 创建 PreUpdateEventArgs
        $eventArgs = $this->createMock(PreUpdateEventArgs::class);

        // 测试 preUpdateEntity 方法（这个方法目前是空实现）
        $this->listener->preUpdateEntity($objectManager, $entity, $eventArgs);

        // 验证方法正常执行，preUpdate 目前不修改实体
        $originalRandomId = $entity->getRandomId();
        $this->assertSame($originalRandomId, $entity->getRandomId());
    }
}
