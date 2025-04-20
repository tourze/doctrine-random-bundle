# Doctrine Random Bundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)

[English](README.md) | [中文](README.zh-CN.md)

一个通过 PHP Attribute 为 Doctrine 实体属性自动生成随机字符串的 Symfony Bundle。

---

## 功能特性

- 为实体属性自动生成随机字符串
- 支持自定义前缀和长度
- 在实体创建（Doctrine prePersist 事件）时自动生成
- 若属性已有值则不会覆盖
- 与 Symfony 自动配置无缝集成

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine Bundle 2.13 或更高版本
- Doctrine ORM 2.20/3.0 或更高版本

## 安装

```bash
composer require tourze/doctrine-random-bundle
```

本 Bundle 由 Symfony Flex 自动注册，无需额外配置。

---

## 快速开始

在实体属性上添加 `RandomStringColumn` Attribute：

```php
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;

class YourEntity
{
    #[RandomStringColumn(prefix: 'user_', length: 20)]
    private string $randomId;

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

当你持久化新实体时，`randomId` 属性会在为空时自动填充：

```php
$entity = new YourEntity();
$entityManager->persist($entity);
$entityManager->flush();
// $entity->getRandomId() 会得到类似 'user_a1b2c3d4e5f6g7h8i9' 的值
```

---

## 配置说明

`RandomStringColumn` 属性支持以下参数：

- `prefix`：随机值的前缀字符串（默认：''）
- `length`：随机字符串长度（默认：16）

---

## 详细说明

- Bundle 内部通过 Doctrine 事件监听器（`RandomStringListener`）在 `prePersist` 阶段为标记有 `RandomStringColumn` 的属性生成随机字符串。
- 若属性已有值则不会自动生成。
- 随机字符串由数字和大小写字母组成。

---

## 贡献指南

欢迎贡献！参与方式：

- 提交 Issue 报告 Bug 或建议新功能
- 提交 Pull Request，需附详细说明和相关测试
- 遵循 PSR 代码规范
- 提交前请用 PHPUnit 完成测试

---

## 许可证

本 Bundle 基于 MIT 协议开源，详见 [LICENSE](LICENSE) 文件。

---

## 更新日志

- v0.1.0：初始版本，支持随机字符串属性和事件监听。

---

## 作者

由 [tourze](https://github.com/tourze) 维护。

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
