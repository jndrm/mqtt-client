<?php

namespace Drmer\Tests\Mqtt\Client;

use Drmer\Mqtt\Client\SwooleClient;

class SwooleClientTest extends TestCase
{
    public function testExistance()
    {
        $this->assertTrue(class_exists('\Drmer\Mqtt\Client\SwooleClient'));
    }

    public function testConstructor()
    {
        $this->assertTrue(null != SwooleClient::v4());
    }
}
