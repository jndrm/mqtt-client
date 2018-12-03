<?php

namespace Drmer\Tests\Mqtt\Client;

use Drmer\Mqtt\Client\ReactClient;
use Drmer\Mqtt\Packet\ConnectionAck;
use Drmer\Mqtt\Packet\Utils\Parser;

class ReactClientTest extends TestCase
{
    public function testExistance()
    {
        $this->assertTrue(class_exists('\Drmer\Mqtt\Client\ReactClient'));
    }

    public function testConstructor()
    {
        $this->assertTrue(null != ReactClient::v4());
    }

    public function testGetLoop()
    {
        $client = ReactClient::v4();
        $this->assertInstanceOf('React\EventLoop\LoopInterface', $client->getLoop());
    }
}
