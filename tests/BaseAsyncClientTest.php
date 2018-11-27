<?php

namespace Drmer\Tests\Mqtt\Client;

use Drmer\Mqtt\Client\BaseAsyncClient;

class BaseAsyncClientTest extends TestCase {
    public function testDirectCallBaseException()
    {
        $this->expectException(
            'RuntimeException',
            'Error Processing Request'
        );

        BaseAsyncClient::v4();
    }
}
