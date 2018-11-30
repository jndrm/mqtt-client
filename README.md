# mqtt-client
Mqtt client in PHP

[![Build Status](https://travis-ci.org/jndrm/mqtt-client.svg?branch=master)](https://travis-ci.org/jndrm/mqtt-client)
[![Maintainability](https://api.codeclimate.com/v1/badges/d0661759a9410f1a2e5d/maintainability)](https://codeclimate.com/github/jndrm/mqtt-client/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/d0661759a9410f1a2e5d/test_coverage)](https://codeclimate.com/github/jndrm/mqtt-client/test_coverage)

## Installation
```sh
composer require drmer/mqtt-client
```

## Usage
```php
use Drmer\Mqtt\Client\SimpleClient;

require __DIR__ . '/../vendor/autoload.php';

$client = SimpleClient::v4();

$client->debug = true;

$client->connect('127.0.0.1', 1883);
$client->publish('hello/world', 'Message from SimpleClient', 1);
$client->disconnect();
$client->close();
```

## Warning
This library is still unstable, please *DO NOT* use this in production.
