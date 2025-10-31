<?php

declare(strict_types=1);

namespace Tourze\DoctrineRandomBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineEntityCheckerBundle\DoctrineEntityCheckerBundle;

class DoctrineRandomBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            DoctrineEntityCheckerBundle::class => ['all' => true],
        ];
    }
}
