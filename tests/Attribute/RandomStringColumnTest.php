<?php

namespace Tourze\DoctrineRandomBundle\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;

/**
 * 测试 RandomStringColumn 属性
 */
class RandomStringColumnTest extends TestCase
{
    /**
     * 测试默认构造函数
     */
    public function testDefaultConstructor(): void
    {
        $attr = new RandomStringColumn();
        $this->assertSame('', $attr->prefix);
        $this->assertSame(16, $attr->length);
    }

    /**
     * 测试自定义参数构造函数
     */
    public function testCustomConstructor(): void
    {
        $attr = new RandomStringColumn(prefix: 'test_', length: 20);
        $this->assertSame('test_', $attr->prefix);
        $this->assertSame(20, $attr->length);
    }
}
