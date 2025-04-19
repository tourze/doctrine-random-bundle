# Doctrine Random Bundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)

[English](README.md) | [中文](README.zh-CN.md)

一个为 Doctrine 实体提供随机字符串生成功能的 Symfony Bundle。

## 功能特性

- 为实体属性生成随机字符串值
- 可配置随机字符串的前缀和长度
- 在实体创建时自动生成值
- 如果属性已有值则跳过生成

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine Bundle 2.13 或更高版本
- Doctrine ORM 2.20/3.0 或更高版本

## 安装

```bash
composer require tourze/doctrine-random-bundle
```

该 Bundle 使用 Symfony 的自动配置功能，安装后会自动启用。

## 使用方法

在实体属性上添加 `RandomStringColumn` 属性：

```php
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;

class YourEntity
{
    #[RandomStringColumn(prefix: 'user_', length: 20)]
    private string $randomId;

    // Getter 和 Setter 方法
    public function getRandomId(): string
    {
        return $this->randomId;
    }

    public function setRandomId(string $randomId): self
    {
        $this->randomId = $randomId;
        return $this;
    }
}
```

## 工作原理

该 Bundle 注册了一个 Doctrine 事件监听器，在 `prePersist` 事件期间自动为标记有 `RandomStringColumn` 属性的属性生成随机字符串值。只有当属性值为空时才会生成随机字符串。

## 配置说明

`RandomStringColumn` 属性接受以下参数：

- `prefix`: 随机值的前缀字符串（默认：''）
- `length`: 随机字符串的长度（默认：16）

## 示例

```php
// 创建一个新实体
$entity = new YourEntity();

// 当实体被持久化时，randomId 属性将自动填充随机字符串
$entityManager->persist($entity);
$entityManager->flush();

// 现在 $entity->getRandomId() 将返回类似 'user_a1b2c3d4e5f6g7h8i9' 的值
```

## 许可证

此 Bundle 基于 MIT 许可证提供。有关更多信息，请参阅 LICENSE 文件。
