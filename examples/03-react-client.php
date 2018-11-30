<?php

use Drmer\Mqtt\Client\ReactClient;

require __DIR__ . '/../vendor/autoload.php';

$client = ReactClient::v4();

// $client->debug = true;

$client->on('connected', function () use ($client) {
    $client->subscribe('hello/world', 1);
    $client->subscribe('drmer/mqtt', 2);
    $client->publish('drmer/mqtt', 'Message from ReactClient with Qos 0', 0);
    $client->publish('drmer/mqtt', 'Message from ReactClient with Qos 1', 1);
    $client->publish('drmer/mqtt', 'Message from ReactClient with Qos 2', 2);

    $client->getLoop()->addTimer(1, function () use ($client) {
        $client->disconnect();
        $client->close();
    });
});

$client->on('message', function ($event, $packet) {
    // Here be your own proccess message logic
    echo $packet->getTopic() . ': ' . $packet->getPayload() . "\n";
});

$ip = gethostbyname('test.mosquitto.org');

$client->connect($ip, 1883, [
    'clientId' => 'drmer-mqtt-react-client',
    'willTopic' => 'drmer/mqtt',
    'willQos' => 1,
    'willMessage' => 'willMessage from ReactClient',
    'keepAlive' => 10,
]);
