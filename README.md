# Doctrine Random Bundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![License](https://img.shields.io/packagist/l/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![Code Coverage](https://codecov.io/gh/tourze/doctrine-random-bundle/branch/main/graph/badge.svg)](https://codecov.io/gh/tourze/doctrine-random-bundle)

[English](README.md) | [中文](README.zh-CN.md)

A Symfony bundle that provides automatic random string generation for Doctrine 
entity properties and random database query functionality with PHP attributes.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [RandomService Usage](#randomservice-usage)
- [Advanced Usage](#advanced-usage)
- [Advanced Details](#advanced-details)
- [Security](#security)
- [Contribution Guide](#contribution-guide)
- [License](#license)
- [Changelog](#changelog)
- [Author](#author)

---

## Features

- **Random String Generation**: Generate random string values for entity properties using PHP attributes
- **Random Database Queries**: Service to fetch random records from database with caching and locking support
- **Configurable**: Customizable prefix and string length for random strings
- **Automatic Generation**: Triggers on entity creation (Doctrine prePersist event)
- **Non-Destructive**: Skips generation if property already has a value
- **Performance Optimized**: Uses caching and distributed locking to prevent conflicts
- **Symfony Integration**: Simple integration with Symfony auto-configuration

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine Bundle 2.13 or higher
- Doctrine ORM 2.20/3.0 or higher

## Installation

```bash
composer require tourze/doctrine-random-bundle
```

This bundle is auto-registered by Symfony Flex. No extra configuration is required.

## Quick Start

Add the `RandomStringColumn` attribute to your entity property:

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

When you persist a new entity, the `randomId` property will be automatically filled if it is empty:

```php
$entity = new YourEntity();
$entityManager->persist($entity);
$entityManager->flush();
// $entity->getRandomId() will return something like 'user_a1b2c3d4e5f6g7h8i9'
```

---

## Configuration

The `RandomStringColumn` attribute accepts the following parameters:

- `prefix`: String prefix for the random value (default: '')
- `length`: Length of the random string (default: 16)

---

## RandomService Usage

The bundle also provides a `RandomService` for fetching random records from the 
database with caching and locking support:

```php
use Tourze\DoctrineRandomBundle\Service\RandomService;

// Inject the service
public function __construct(
    private readonly RandomService $randomService,
    private readonly EntityManagerInterface $entityManager,
) {}

// Get random records
$queryBuilder = $this->entityManager->createQueryBuilder()
    ->select('u')
    ->from(User::class, 'u')
    ->where('u.active = :active')
    ->setParameter('active', true);

// Get 3 random users
$randomUsers = $this->randomService->getRandomResult($queryBuilder, 3);

foreach ($randomUsers as $user) {
    // Process each random user
}
```

### RandomService Features

- **Distributed Locking**: Prevents multiple processes from getting the same random record
- **Caching**: Caches ID lists for better performance (1-minute TTL)
- **Flexible Querying**: Works with any QueryBuilder with WHERE conditions
- **Configurable Range**: Limits the cached ID range to optimize memory usage

---

## Advanced Usage

### Custom Random String Generation

You can use multiple `RandomStringColumn` attributes on different properties:

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

### Advanced RandomService Usage

The `RandomService` supports complex queries and offers fine-grained control:

```php
// Get random products with specific conditions
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

## Advanced Details

- The bundle uses a Doctrine event listener (`RandomStringListener`) to automatically 
  generate random strings for properties marked with the `RandomStringColumn` attribute 
  during the `prePersist` event.
- If the property already has a value, it will not be overwritten.
- The random string is composed of numbers and upper/lowercase letters.
- The `RandomService` uses Symfony's Lock component and Cache component for 
  performance optimization.

---

## Security

- Random strings are generated using PHP's `random_int()` function for cryptographic security
- The bundle respects existing property values and never overwrites them
- Distributed locking prevents race conditions in concurrent environments

---

## Contribution Guide

Contributions are welcome! To contribute:

- Open an issue for bug reports or feature requests.
- Submit a pull request with clear description and relevant tests.
- Follow PSR coding standards.
- Run tests with PHPUnit before submitting.

---

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## Changelog

- v0.1.0: Initial release with random string attribute and random query service support.

---

## Author

Maintained by [tourze](https://github.com/tourze).
