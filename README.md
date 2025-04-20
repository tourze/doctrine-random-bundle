# Doctrine Random Bundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)

[English](README.md) | [中文](README.zh-CN.md)

A Symfony bundle that provides automatic random string generation for Doctrine entity properties using PHP attributes.

---

## Features

- Generate random string values for entity properties
- Configurable prefix and string length
- Automatic value generation on entity creation (Doctrine prePersist event)
- Skips generation if property already has a value
- Simple integration with Symfony auto-configuration

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

---

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

## Advanced Details

- The bundle uses a Doctrine event listener (`RandomStringListener`) to automatically generate random strings for properties marked with the `RandomStringColumn` attribute during the `prePersist` event.
- If the property already has a value, it will not be overwritten.
- The random string is composed of numbers and upper/lowercase letters.

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

- v0.1.0: Initial release with random string attribute and event listener support.

---

## Author

Maintained by [tourze](https://github.com/tourze).

## Configuration

The `RandomStringColumn` attribute accepts the following parameters:

- `prefix`: String prefix for the random value (default: '')
- `length`: Length of the random string (default: 16)

## Example

```php
// Create a new entity
$entity = new YourEntity();

// The randomId property will be automatically filled with a random string
// when the entity is persisted
$entityManager->persist($entity);
$entityManager->flush();

// Now $entity->getRandomId() will return something like 'user_a1b2c3d4e5f6g7h8i9'
```

## License

This bundle is available under the MIT license. See the LICENSE file for more information.
