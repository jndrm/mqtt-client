<?php

namespace Drmer\Tests\Mqtt\Client;

use Drmer\Mqtt\Client\BaseClient;

class BaseClientTest extends TestCase
{
    public function testDirectCallBaseException()
    {
        $this->expectException(
            'RuntimeException',
            'Could not instance from BaseClient'
        );

        BaseClient::v4();
    }
}
