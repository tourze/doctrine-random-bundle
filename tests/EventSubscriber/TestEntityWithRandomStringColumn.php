<?php

declare(strict_types=1);

namespace Tourze\DoctrineRandomBundle\Tests\EventSubscriber;

use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;

/**
 * 测试实体，标记了 RandomStringColumn 属性
 */
class TestEntityWithRandomStringColumn
{
    #[RandomStringColumn(prefix: 'test_', length: 10)]
    private ?string $randomId = null;

    public function getRandomId(): ?string
    {
        return $this->randomId;
    }

    public function setRandomId(?string $randomId): void
    {
        $this->randomId = $randomId;
    }
}
