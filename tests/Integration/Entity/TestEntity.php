<?php

namespace Tourze\DoctrineRandomBundle\Tests\Integration\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;

#[ORM\Entity]
#[ORM\Table(name: 'test_entity', options: ['comment' => '随机字符串集成测试实体'])]
class TestEntity implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '带前缀的随机ID'])]
    #[RandomStringColumn(prefix: 'test_', length: 20)]
    private string $randomId = '';

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '简单随机字符串'])]
    #[RandomStringColumn(length: 16)]
    private string $simpleRandom = '';

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '短编码随机字符串'])]
    #[RandomStringColumn(prefix: 'short_', length: 10)]
    private string $shortCode = '';

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '实体名称'])]
    private string $name = '';

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '数值'])]
    private int $value = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRandomId(): string
    {
        return $this->randomId;
    }

    public function setRandomId(string $randomId): self
    {
        $this->randomId = $randomId;
        return $this;
    }

    public function getSimpleRandom(): string
    {
        return $this->simpleRandom;
    }

    public function setSimpleRandom(string $simpleRandom): self
    {
        $this->simpleRandom = $simpleRandom;
        return $this;
    }

    public function getShortCode(): string
    {
        return $this->shortCode;
    }

    public function setShortCode(string $shortCode): self
    {
        $this->shortCode = $shortCode;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name !== '' ? $this->name : 'TestEntity #' . $this->id;
    }
}
