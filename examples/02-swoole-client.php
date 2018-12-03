<?php

use Drmer\Mqtt\Client\SwooleClient;
use Swoole\Timer;

require __DIR__ . '/../vendor/autoload.php';

$client = SwooleClient::v4();

// $client->debug = true;

$client->connect('test.mosquitto.org', 1883, [
    'clientId' => 'drmer-mqtt-swoole-client',
    'willTopic' => 'test',
    'willQos' => 1,
    'willMessage' => 'hello',
    'keepAlive' => 10,
]);

$client->on('connected', function () use ($client) {
    $client->subscribe('hello/world', 1);
    $client->subscribe('drmer/mqtt', 2);
    $client->publish('drmer/mqtt', 'Message from ReactClient with Qos 0', 0);
    $client->publish('drmer/mqtt', 'Message from SwooleClient with Qos 1', 1);
    $client->publish('drmer/mqtt', 'Message from SwooleClient with Qos 2', 2);
    Timer::after(1000, function () use ($client) {
        $client->disconnect();
        $client->close();
    });
});

$client->on('message', function ($event, $packet) {
    // Here be your own proccess message logic
    echo $packet->getTopic() . ': ' . $packet->getPayload() . "\n";
});
