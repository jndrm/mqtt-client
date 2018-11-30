<?php

namespace Drmer\Tests\Mqtt\Client;

use Drmer\Mqtt\Client\SwooleClient;
use Drmer\Mqtt\Packet\Protocol\Version4;

class SwooleClientTest extends TestCase
{
    public function testExistance()
    {
        $this->assertTrue(class_exists('\Drmer\Mqtt\Client\SwooleClient'));
    }

    public function testConstructor()
    {
        $instance = new SwooleClient(new Version4());
        $this->assertInstanceOf(SwooleClient::class, $instance);
    }

    public function testV4()
    {
        $instance = SwooleClient::v4();
        $this->assertInstanceOf(SwooleClient::class, $instance);
    }
}
