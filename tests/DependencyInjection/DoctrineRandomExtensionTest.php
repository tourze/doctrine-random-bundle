<?php

namespace Tourze\DoctrineRandomBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\DoctrineRandomBundle\DependencyInjection\DoctrineRandomExtension;
use Tourze\DoctrineRandomBundle\EventSubscriber\RandomStringListener;

/**
 * 测试依赖注入扩展
 */
class DoctrineRandomExtensionTest extends TestCase
{
    /**
     * 测试服务加载
     */
    public function testServiceLoading(): void
    {
        $container = new ContainerBuilder();
        $extension = new DoctrineRandomExtension();

        $extension->load([], $container);

        // 测试事件订阅器服务是否被正确注册
        $this->assertTrue($container->has(RandomStringListener::class));

        // 测试随机服务是否被正确注册
        $this->assertTrue($container->hasDefinition('Tourze\DoctrineRandomBundle\Service\RandomService') ||
            $container->hasAlias('Tourze\DoctrineRandomBundle\Service\RandomService'));
    }
}
