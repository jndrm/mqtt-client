<?php

namespace Drmer\Tests\Mqtt\Client;

use Drmer\Mqtt\Client\ReactClient;
use Drmer\Mqtt\Packet\ConnectionAck;
use Drmer\Mqtt\Packet\Utils\Parser;

class ReactClientTest extends TestCase {
    public function testExistance()
    {
        $this->assertTrue(class_exists('\Drmer\Mqtt\Client\ReactClient'));
    }

    public function testConstructor()
    {
        $this->assertTrue(null != ReactClient::v4());
    }

    public function testGetNextPacket()
    {
        $connectAckPacket = new ConnectionAck();
        $client = ReactClient::v4();
        foreach ($client->getNextPacket($connectAckPacket->get()) as $data) {
            $packet = Parser::parse($data);
            $this->assertInstanceOf('Drmer\Mqtt\Packet\ConnectionAck', $packet);
        }
    }
}
