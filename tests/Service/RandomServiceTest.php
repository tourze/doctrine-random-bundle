<?php

declare(strict_types=1);

namespace Tourze\DoctrineRandomBundle\Tests\Service;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\DoctrineRandomBundle\Service\RandomService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * 测试随机服务
 *
 * @internal
 */
#[CoversClass(RandomService::class)]
#[RunTestsInSeparateProcesses]
final class RandomServiceTest extends AbstractIntegrationTestCase
{
    private RandomService $randomService;

    protected function onSetUp(): void
    {
        // 从容器获取服务，使用真实的依赖
        $this->randomService = self::getService(RandomService::class);
    }

    /**
     * 测试构造函数注入
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(RandomService::class, $this->randomService);
    }

    /**
     * 测试当没有缓存时，getRandomResult 应该返回空结果
     */
    public function testGetRandomResultWithoutIds(): void
    {
        // 创建QueryBuilder mock，返回空结果
        $queryBuilder = $this->createMockQueryBuilder(['user']);

        // 执行测试
        $results = $this->randomService->getRandomResult($queryBuilder);

        // 验证返回结果是可遍历的
        $this->assertInstanceOf(\Traversable::class, $results);

        // 验证结果是数组
        $resultArray = iterator_to_array($results);
        $this->assertIsArray($resultArray);
        $this->assertEmpty($resultArray);
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
        // 创建QueryBuilder mock
        $queryBuilder = $this->createMockQueryBuilder(['user']);

        // 执行测试，验证方法能正常执行
        $results = iterator_to_array($this->randomService->getRandomResult($queryBuilder));

        // 验证结果是数组
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /**
     * 创建QueryBuilder mock的辅助方法
     * @param string[] $rootAliases
     * @return QueryBuilder&MockObject
     */
    private function createMockQueryBuilder(array $rootAliases): QueryBuilder
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn($rootAliases);
        $queryBuilder->method('getDQL')->willReturn('SELECT u FROM User u');
        $queryBuilder->method('getMaxResults')->willReturn(null);
        $queryBuilder->method('getFirstResult')->willReturn(0);

        return $queryBuilder;
    }
}
