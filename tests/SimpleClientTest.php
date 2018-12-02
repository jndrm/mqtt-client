<?php

namespace Drmer\Tests\Mqtt\Client;

use Drmer\Mqtt\Client\SimpleClient;
use Drmer\Mqtt\Packet\Protocol\Version4;

class SimpleClientTest extends TestCase
{
    public function testExistance()
    {
        $this->assertTrue(class_exists('\Drmer\Mqtt\Client\SimpleClient'));
    }

    public function testConstructor()
    {
        $instance = new SimpleClient(new Version4());
        $this->assertInstanceOf(SimpleClient::class, $instance);
    }

    public function testStaticV4()
    {
        $instance = SimpleClient::v4();
        $this->assertInstanceOf(SimpleClient::class, $instance);
    }

    public function testConnect()
    {
        $client = SimpleClient::v4();
        $client->connect('127.0.0.1', 1883);
        $this->assertTrue(true);
    }
}
