<?php

use Drmer\Mqtt\Client\SimpleClient;

require __DIR__ . '/../vendor/autoload.php';

$client = SimpleClient::v4();

// $client->debug = true;

$client->connect('test.mosquitto.org', 1883, [
    'clientId' => 'drmer-mqtt-simple-client'
]);
$client->publish('drmer/mqtt', 'Message from SimpleClient', 1);
$client->disconnect();
$client->close();
