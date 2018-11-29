<?php

use Drmer\Mqtt\Client\ReactClient;

require __DIR__ . '/../vendor/autoload.php';

$client = ReactClient::v4();

$client->debug = true;

$client->on('connected', function () use ($client) {
    $client->publish('hello/world', 'Message from ReactClient', 2);
});

$client->connect('127.0.0.1', 1883, [
    'clientId' => 'react-client',
]);
