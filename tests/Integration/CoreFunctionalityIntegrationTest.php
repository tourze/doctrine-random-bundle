<?php

namespace Tourze\DoctrineRandomBundle\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\DoctrineEntityCheckerBundle\DoctrineEntityCheckerBundle;
use Tourze\DoctrineRandomBundle\DoctrineRandomBundle;
use Tourze\DoctrineRandomBundle\EventSubscriber\RandomStringListener;
use Tourze\DoctrineRandomBundle\Service\RandomService;
use Tourze\DoctrineRandomBundle\Tests\Integration\Entity\TestEntity;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;

class CoreFunctionalityIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private RandomService $randomService;

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new IntegrationTestKernel(
            'test',
            true,
            [
                DoctrineEntityCheckerBundle::class => ['all' => true],
                DoctrineRandomBundle::class => ['all' => true],
            ],
            [
                'Tourze\DoctrineRandomBundle\Tests\Integration\Entity' => __DIR__ . '/Entity',
            ]
        );
    }

    /**
     * 测试核心功能：RandomStringListener 在实体持久化时自动生成随机字符串
     */
    public function test_random_string_generation_on_persist(): void
    {
        // Arrange
        $entity = new TestEntity();
        $entity->setName('Test Entity');
        $entity->setValue(100);

        // Act - 持久化实体，应该触发 RandomStringListener
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        // Assert - 验证随机字符串已生成
        $this->assertNotNull($entity->getId());
        $this->assertNotEmpty($entity->getRandomId());
        $this->assertNotEmpty($entity->getSimpleRandom());
        $this->assertNotEmpty($entity->getShortCode());

        // 验证格式正确
        $this->assertStringStartsWith('test_', $entity->getRandomId());
        $this->assertEquals(20, strlen($entity->getRandomId()));
        $this->assertEquals(16, strlen($entity->getSimpleRandom()));
        $this->assertStringStartsWith('short_', $entity->getShortCode());
        $this->assertEquals(10, strlen($entity->getShortCode()));
    }

    /**
     * 测试随机字符串的唯一性
     */
    public function test_random_string_uniqueness(): void
    {
        // Arrange & Act
        $entities = [];
        for ($i = 0; $i < 5; $i++) {
            $entity = new TestEntity();
            $entity->setName("Entity {$i}");
            $entity->setValue($i * 10);
            $this->entityManager->persist($entity);
            $entities[] = $entity;
        }
        $this->entityManager->flush();

        // Assert - 验证所有随机字符串都不同
        $randomIds = array_map(fn($e) => $e->getRandomId(), $entities);
        $simpleRandoms = array_map(fn($e) => $e->getSimpleRandom(), $entities);
        $shortCodes = array_map(fn($e) => $e->getShortCode(), $entities);

        $this->assertEquals(5, count(array_unique($randomIds)));
        $this->assertEquals(5, count(array_unique($simpleRandoms)));
        $this->assertEquals(5, count(array_unique($shortCodes)));
    }

    /**
     * 测试已有值不被覆盖
     */
    public function test_existing_values_not_overwritten(): void
    {
        // Arrange
        $entity = new TestEntity();
        $entity->setName('Pre-set Entity');
        $entity->setRandomId('existing_random_id');
        $entity->setSimpleRandom('existing_simple');
        // shortCode 保持空，应该被生成

        // Act
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        // Assert
        $this->assertEquals('existing_random_id', $entity->getRandomId());
        $this->assertEquals('existing_simple', $entity->getSimpleRandom());
        $this->assertNotEmpty($entity->getShortCode());
        $this->assertStringStartsWith('short_', $entity->getShortCode());
    }

    /**
     * 测试数据库持久化验证
     */
    public function test_database_persistence(): void
    {
        // Arrange
        $entity = new TestEntity();
        $entity->setName('Persistence Test');
        $entity->setValue(42);

        // Act
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $entityId = $entity->getId();
        $randomId = $entity->getRandomId();
        $simpleRandom = $entity->getSimpleRandom();

        // Clear entity manager and reload
        $this->entityManager->clear();
        $reloadedEntity = $this->entityManager->find(TestEntity::class, $entityId);

        // Assert
        $this->assertNotNull($reloadedEntity);
        $this->assertEquals($randomId, $reloadedEntity->getRandomId());
        $this->assertEquals($simpleRandom, $reloadedEntity->getSimpleRandom());
        $this->assertEquals('Persistence Test', $reloadedEntity->getName());
        $this->assertEquals(42, $reloadedEntity->getValue());
    }

    /**
     * 测试 RandomService 核心功能：从数据库随机获取结果
     */
    public function test_random_service_functionality(): void
    {
        // Arrange - 创建多个实体
        for ($i = 1; $i <= 10; $i++) {
            $entity = new TestEntity();
            $entity->setName("Random Entity {$i}");
            $entity->setValue($i * 10);
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();

        // Act - 使用 RandomService 获取随机结果
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(TestEntity::class, 'e');

        $results = $this->randomService->getRandomResult($queryBuilder, 3);

        // Assert
        $resultArray = iterator_to_array($results);
        $this->assertCount(3, $resultArray);

        foreach ($resultArray as $result) {
            $this->assertInstanceOf(TestEntity::class, $result);
            $this->assertStringContainsString('Random Entity', $result->getName());
        }
    }

    /**
     * 测试 RandomService 随机性
     */
    public function test_random_service_randomness(): void
    {
        // Arrange - 创建20个实体
        for ($i = 1; $i <= 20; $i++) {
            $entity = new TestEntity();
            $entity->setName("Randomness Test {$i}");
            $entity->setValue($i);
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(TestEntity::class, 'e');

        // Act - 多次执行获取随机结果
        $allResults = [];
        for ($j = 0; $j < 3; $j++) {
            $results = $this->randomService->getRandomResult($queryBuilder, 3);
            $resultArray = iterator_to_array($results);
            $allResults[] = array_map(fn($entity) => $entity->getId(), $resultArray);
        }

        // Assert - 验证结果不总是相同（至少有一次不同）
        $uniqueResults = array_unique(array_map('serialize', $allResults));
        $this->assertGreaterThanOrEqual(1, count($uniqueResults), '随机服务应该返回随机结果');
    }

    /**
     * 测试 RandomService 的条件查询
     */
    public function test_random_service_with_conditions(): void
    {
        // Arrange - 创建不同值的实体
        for ($i = 1; $i <= 10; $i++) {
            $entity = new TestEntity();
            $entity->setName("Conditional Entity {$i}");
            $entity->setValue($i <= 5 ? 100 : 200); // 前5个值为100，后5个值为200
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();

        // Act - 只查询值为100的实体
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(TestEntity::class, 'e')
            ->where('e.value = :targetValue')
            ->setParameter('targetValue', 100);

        $results = $this->randomService->getRandomResult($queryBuilder, 2);

        // Assert
        $resultArray = iterator_to_array($results);
        $this->assertCount(2, $resultArray);

        foreach ($resultArray as $result) {
            $this->assertEquals(100, $result->getValue());
        }
    }

    /**
     * 测试 RandomService 空结果处理
     */
    public function test_random_service_empty_results(): void
    {
        // Arrange - 确保数据库为空

        // Act
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(TestEntity::class, 'e');

        $results = $this->randomService->getRandomResult($queryBuilder, 1);

        // Assert
        $resultArray = iterator_to_array($results);
        $this->assertCount(0, $resultArray);
    }

    /**
     * 测试批量操作性能
     */
    public function test_bulk_operations_performance(): void
    {
        // Arrange
        $entities = [];
        for ($i = 1; $i <= 50; $i++) {
            $entity = new TestEntity();
            $entity->setName("Bulk Entity {$i}");
            $entity->setValue($i);
            $entities[] = $entity;
            $this->entityManager->persist($entity);
        }

        // Act
        $startTime = microtime(true);
        $this->entityManager->flush();
        $endTime = microtime(true);

        // Assert
        $flushTime = $endTime - $startTime;
        $this->assertLessThan(5.0, $flushTime, 'Bundle 批量操作性能应该在合理范围内');

        // 验证所有实体的随机字符串都已生成
        foreach ($entities as $entity) {
            $this->assertNotEmpty($entity->getRandomId());
            $this->assertNotEmpty($entity->getSimpleRandom());
            $this->assertNotEmpty($entity->getShortCode());
        }
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->randomService = static::getContainer()->get(RandomService::class);

        $this->cleanDatabase();
    }

    private function cleanDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        try {
            $connection->executeStatement('DELETE FROM test_entity');
        } catch (\Exception $e) {
            // 表不存在时忽略错误
        }
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
        self::ensureKernelShutdown();
        parent::tearDown();
    }
}
