<?php

declare(strict_types=1);

namespace Tourze\DoctrineRandomBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DoctrineRandomBundle\DependencyInjection\DoctrineRandomExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * 测试依赖注入扩展
 *
 * @internal
 */
#[CoversClass(DoctrineRandomExtension::class)]
final class DoctrineRandomExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
