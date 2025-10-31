# Doctrine Random Bundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![License](https://img.shields.io/packagist/l/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![Code Coverage](https://codecov.io/gh/tourze/doctrine-random-bundle/branch/main/graph/badge.svg)](https://codecov.io/gh/tourze/doctrine-random-bundle)

[English](README.md) | [中文](README.zh-CN.md)

一个通过 PHP Attribute 为 Doctrine 实体属性自动生成随机字符串并提供随机数据库查询功能的 Symfony Bundle。

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装](#安装)
- [快速开始](#快速开始)
- [配置说明](#配置说明)
- [RandomService 使用方法](#randomservice-使用方法)
- [高级用法](#高级用法)
- [详细说明](#详细说明)
- [安全性](#安全性)
- [贡献指南](#贡献指南)
- [许可证](#许可证)
- [更新日志](#更新日志)
- [作者](#作者)

---

## 功能特性

- **随机字符串生成**：使用 PHP Attribute 为实体属性自动生成随机字符串
- **随机数据库查询**：提供带缓存和锁定支持的随机记录查询服务
- **可配置**：支持自定义前缀和长度
- **自动生成**：在实体创建（Doctrine prePersist 事件）时自动生成
- **非破坏性**：若属性已有值则不会覆盖
- **性能优化**：使用缓存和分布式锁定防止冲突
- **Symfony 集成**：与 Symfony 自动配置无缝集成

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

## RandomService 使用方法

Bundle 还提供了 `RandomService` 用于从数据库获取随机记录，支持缓存和锁定：

```php
use Tourze\DoctrineRandomBundle\Service\RandomService;

// 注入服务
public function __construct(
    private readonly RandomService $randomService,
    private readonly EntityManagerInterface $entityManager,
) {}

// 获取随机记录
$queryBuilder = $this->entityManager->createQueryBuilder()
    ->select('u')
    ->from(User::class, 'u')
    ->where('u.active = :active')
    ->setParameter('active', true);

// 获取 3 个随机用户
$randomUsers = $this->randomService->getRandomResult($queryBuilder, 3);

foreach ($randomUsers as $user) {
    // 处理每个随机用户
}
```

### RandomService 功能特性

- **分布式锁定**：防止多个进程获得相同的随机记录
- **缓存支持**：缓存 ID 列表以提高性能（1分钟 TTL）
- **灵活查询**：支持任何带 WHERE 条件的 QueryBuilder
- **可配置范围**：限制缓存的 ID 范围以优化内存使用

---

## 高级用法

### 自定义随机字符串生成

你可以在不同属性上使用多个 `RandomStringColumn` 属性：

```php
class Product
{
    #[RandomStringColumn(prefix: 'SKU-', length: 12)]
    private string $sku;

    #[RandomStringColumn(length: 8)]
    private string $code;

    #[RandomStringColumn(prefix: 'REF_', length: 16)]
    private string $reference;
}
```

### RandomService 高级用法

`RandomService` 支持复杂查询并提供细粒度控制：

```php
// 获取满足特定条件的随机产品
$queryBuilder = $this->entityManager->createQueryBuilder()
    ->select('p')
    ->from(Product::class, 'p')
    ->join('p.category', 'c')
    ->where('p.active = :active')
    ->andWhere('c.featured = :featured')
    ->andWhere('p.stock > :minStock')
    ->setParameter('active', true)
    ->setParameter('featured', true)
    ->setParameter('minStock', 0);

$randomProducts = $this->randomService->getRandomResult($queryBuilder, 5);
```

---

## 详细说明

- Bundle 内部通过 Doctrine 事件监听器（`RandomStringListener`）在 `prePersist` 阶段为标记有 `RandomStringColumn` 的属性生成随机字符串。
- 若属性已有值则不会自动生成。
- 随机字符串由数字和大小写字母组成。
- `RandomService` 使用 Symfony 的 Lock 组件和 Cache 组件进行性能优化。

---

## 安全性

- 随机字符串使用 PHP 的 `random_int()` 函数生成，保证密码学安全性
- Bundle 尊重已有属性值，绝不覆盖
- 分布式锁定防止并发环境中的竞态条件

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

- v0.1.0：初始版本，支持随机字符串属性和随机查询服务。

---

## 作者

由 [tourze](https://github.com/tourze) 维护。
