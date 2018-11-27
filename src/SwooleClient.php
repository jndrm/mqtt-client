<?php

namespace Drmer\Mqtt\Client;

use Swoole\Client as SwooleSocket;

class SwooleClient extends BaseAsyncClient {
    public function socketOpen($host, $port)
    {
        $client = $this->socket = new SwooleSocket(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $client->set([
            'open_mqtt_protocol' => true,
        ]);

        $client->on('connect', function() {
            $this->emit('connect');
        });

        $client->on("receive", function(\swoole_client $cli, $data) {
            $this->onReceive($data);
        });

        $client->on("error", function(\swoole_client $cli) {
            echo "error\n";
        });
        $client->on("close", function(\swoole_client $cli) {
            echo "Connection close\n";
        });

        $this->socket->connect($host, $port, 30);
    }

    public function socketSend($data)
    {
        $this->socket->send($data);
    }

    public function socketClose()
    {
        $this->socket->close();
    }

    public function isConnected()
    {
        return $this->socket->isConnected();
    }
}
