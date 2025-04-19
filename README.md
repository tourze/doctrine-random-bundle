# Doctrine Random Bundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-random-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-random-bundle)

[English](README.md) | [中文](README.zh-CN.md)

A Symfony bundle that provides random string generation capabilities for Doctrine entities.

## Features

- Generate random string values for entity properties
- Configurable prefix and length for random strings
- Automatic value generation on entity creation
- Skip generation if property already has a value

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine Bundle 2.13 or higher
- Doctrine ORM 2.20/3.0 or higher

## Installation

```bash
composer require tourze/doctrine-random-bundle
```

The bundle uses Symfony's auto-configuration, so it will be automatically enabled once installed.

## Usage

Add the `RandomStringColumn` attribute to your entity properties:

```php
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;

class YourEntity
{
    #[RandomStringColumn(prefix: 'user_', length: 20)]
    private string $randomId;

    // Getters and setters
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

## How It Works

The bundle registers a Doctrine event listener that automatically generates random string values for properties marked with the `RandomStringColumn` attribute during the `prePersist` event. The random string is only generated if the property value is empty.

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
