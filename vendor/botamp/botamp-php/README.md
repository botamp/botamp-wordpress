# Botamp PHP SDK

[![Build Status](https://travis-ci.org/botamp/botamp-php.svg?branch=master)](https://travis-ci.org/botamp/botamp-php)
[![Coverage Status](https://coveralls.io/repos/github/botamp/botamp-php/badge.svg?branch=master)](https://coveralls.io/github/botamp/botamp-php?branch=master)
[![Code Climate](https://codeclimate.com/github/botamp/botamp-php/badges/gpa.svg)](https://codeclimate.com/github/botamp/botamp-php)
[![Latest Stable Version](https://poser.pugx.org/botamp/botamp-php/v/stable)](https://packagist.org/packages/botamp/botamp-php)
[![Total Downloads](https://poser.pugx.org/botamp/botamp-php/downloads)](https://packagist.org/packages/botamp/botamp-php)
[![License](https://poser.pugx.org/botamp/botamp-php/license)](https://packagist.org/packages/botamp/botamp-php)

Botamp is an autonomous AI-enabled chat assistant which interacts seamlessly with your customers, keeps them engaged and makes them buy more from you, while you can focus on serving them.

You can sign up for a Botamp account at https://botamp.com.

## Requirements

PHP 5.6 and later (previous PHP versions may work but untested), HHVM

## Composer

You can install the SDK via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require botamp/botamp-php
```

To be able to use it, require Composer [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):

```php
require_once('vendor/autoload.php');
```

## Getting Started

Here goes a simple usage:

```php
$botamp = new Botamp\Client(YOUR_API_KEY);
$entities = $botamp->entities->all();

foreach($entities as $entity)
{
    echo $entity['name'];
}
```

## Documentation

Please see https://app.botamp.com/docs/api for up-to-date documentation.

## Development

Install dependencies:

``` bash
composer install
```

## Tests


Install dependencies as mentioned above (which will resolve [PHPUnit](http://packagist.org/packages/phpunit/phpunit)), then you can run the test suite:

```bash
./vendor/bin/phpunit
```

Or to run an individual test file:

```bash
./vendor/bin/phpunit tests/ClientTest.php
```
