<?php

declare(strict_types=1);

namespace Tourze\DoctrineRandomBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DoctrineRandomBundle\DoctrineRandomBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(DoctrineRandomBundle::class)]
#[RunTestsInSeparateProcesses]
final class DoctrineRandomBundleTest extends AbstractBundleTestCase
{
}
