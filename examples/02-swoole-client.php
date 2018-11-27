<?php

use Drmer\Mqtt\Client\SwooleClient;

require __DIR__ . '/../vendor/autoload.php';

$client = SwooleClient::v4();

$client->debug = true;

$client->connect('127.0.0.1', 1883, [
    'clientId' => 'swoole-client',
]);

$client->on('connected', function () use ($client) {
    $client->subscribe('test', 2);
    $client->publish('test', 'swoole1', 2);
    sleep(10);
    $client->disconnect();
    $client->close();
});
