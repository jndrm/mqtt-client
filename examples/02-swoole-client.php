<?php

use Drmer\Mqtt\Client\SwooleClient;

require __DIR__ . '/../vendor/autoload.php';

$client = SwooleClient::v4();

// $client->debug = true;

$client->connect('127.0.0.1', 1883, [
    'clientId' => 'swoole-client',
    'willTopic' => 'hello/world',
    'willQos' => 1,
    'willMessage' => 'hello',
    'keepAlive' => 10,
]);

$client->on('connected', function () use ($client) {
    $client->subscribe('test', 2);
    $client->subscribe('hello/world', 1);
    $client->publish('test', 'Message from ReactClient with Qos 0', 0);
    $client->publish('test', 'Message from SwooleClient with Qos 1', 1);
    $client->publish('test', 'Message from SwooleClient with Qos 2', 2);
    // $client->disconnect();
    // $client->close();
});

$client->on('message', function ($event, $packet) {
    // Here be your own proccess message logic
    echo $packet->getTopic() . ': ' . $packet->getPayload() . "\n";
});
