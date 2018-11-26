<?php

namespace Drmer\Tests\Mqtt\Client;

class SimpleClientTest extends TestCase {
    public function testExistance()
    {
        $this->assertTrue(class_exists('\Drmer\Mqtt\Client\SimpleClient'));
    }
}
