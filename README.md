# mqtt-client
Mqtt client in PHP

## Installation
```sh
composer require drmer\mqtt-client
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
This library is still unstable, please *DON'T* use it in production.
