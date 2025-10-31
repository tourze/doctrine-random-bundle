<?php

declare(strict_types=1);

namespace Tourze\DoctrineRandomBundle\Attribute;

/**
 * 生成随机字符串
 */
#[\Attribute(flags: \Attribute::TARGET_PROPERTY)]
class RandomStringColumn
{
    public function __construct(
        public string $prefix = '', // 前缀
        public int $length = 16, // 长度限制
    ) {
    }
}
