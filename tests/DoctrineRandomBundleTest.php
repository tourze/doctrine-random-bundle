<?php

namespace Tourze\DoctrineRandomBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineEntityCheckerBundle\DoctrineEntityCheckerBundle;
use Tourze\DoctrineRandomBundle\DoctrineRandomBundle;

/**
 * 测试 DoctrineRandomBundle 核心功能
 */
class DoctrineRandomBundleTest extends TestCase
{
    /**
     * 测试 Bundle 依赖关系
     */
    public function testBundleDependencies(): void
    {
        $dependencies = DoctrineRandomBundle::getBundleDependencies();
        $this->assertIsArray($dependencies);
        $this->assertArrayHasKey(DoctrineEntityCheckerBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[DoctrineEntityCheckerBundle::class]);
    }
}
