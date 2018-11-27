<?php

namespace Drmer\Tests\Mqtt\Client;

use Drmer\Mqtt\Client\BaseSyncClient;

class BaseSyncClientTest extends TestCase {
    public function testDirectCallBaseException()
    {
        $this->expectException(
            'RuntimeException',
            'Error Processing Request'
        );

        BaseSyncClient::v4();
    }
}
