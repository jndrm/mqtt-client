<?php

namespace Drmer\Tests\Mqtt\Client;

use Drmer\Mqtt\Client\ReactClient;

class ReactClientTest extends TestCase {
    public function testExistance()
    {
        $this->assertTrue(class_exists('\Drmer\Mqtt\Client\ReactClient'));
    }

    public function testConstructor()
    {
        $this->assertTrue(null != ReactClient::v4());
    }
}
