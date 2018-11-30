<?php

use Drmer\Mqtt\Client\ReactClient;

require __DIR__ . '/../vendor/autoload.php';

$client = ReactClient::v4();

// $client->debug = true;

$client->on('connected', function () use ($client) {
    $client->subscribe('test', 2);
    $client->subscribe('hello/world', 1);
    $client->publish('test', 'Message from ReactClient with Qos 0', 0);
    $client->publish('test', 'Message from ReactClient with Qos 1', 1);
    $client->publish('test', 'Message from ReactClient with Qos 2', 2);
});

$client->on('message', function ($event, $packet) {
    // Here be your own proccess message logic
    echo $packet->getTopic() . ': ' . $packet->getPayload() . "\n";
});

$client->connect('127.0.0.1', 1883, [
    'clientId' => 'react-client',
    'willTopic' => 'hello/world',
    'willQos' => 1,
    'willMessage' => 'hello',
    'keepAlive' => 10,
]);
