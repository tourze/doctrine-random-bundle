# Doctrine Random Bundle

[English](#english) | [中文](#中文)

## English

A Symfony bundle that provides random data generation capabilities for Doctrine entities.

### Features

- Generate random string values for entity properties
- Configurable prefix and length for random strings
- Automatic value generation on entity creation/update

### Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine Bundle 2.13 or higher

### Installation

```bash
composer require tourze/doctrine-random-bundle
```

### Usage

Add the `RandomStringColumn` attribute to your entity properties:

```php
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;

class YourEntity
{
    #[RandomStringColumn(prefix: 'user_', length: 20)]
    private string $randomId;
}
```

### Configuration

The `RandomStringColumn` attribute accepts the following parameters:

- `prefix`: String prefix for the random value (default: '')
- `length`: Length of the random string (default: 16)

## 中文

一个为 Doctrine 实体提供随机数据生成功能的 Symfony Bundle。

### 功能特性

- 为实体属性生成随机字符串值
- 可配置随机字符串的前缀和长度
- 在实体创建/更新时自动生成值

### 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine Bundle 2.13 或更高版本

### 安装

```bash
composer require tourze/doctrine-random-bundle
```

### 使用方法

在实体属性上添加 `RandomStringColumn` 属性：

```php
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;

class YourEntity
{
    #[RandomStringColumn(prefix: 'user_', length: 20)]
    private string $randomId;
}
```

### 配置说明

`RandomStringColumn` 属性接受以下参数：

- `prefix`: 随机值的前缀字符串（默认：''）
- `length`: 随机字符串的长度（默认：16）
