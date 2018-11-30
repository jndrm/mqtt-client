<?php

namespace Drmer\Tests\Mqtt\Client;

use Drmer\Mqtt\Client\SimpleClient;

class SimpleClientTest extends TestCase
{
    public function testExistance()
    {
        $this->assertTrue(class_exists('\Drmer\Mqtt\Client\SimpleClient'));
    }

    public function testConstructor()
    {
        $this->assertTrue(null != SimpleClient::v4());
    }
}
