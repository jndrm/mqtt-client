<?php

namespace Drmer\Mqtt\Client;

use Swoole\Client as SwooleSocket;
use Swoole\Timer;

class SwooleClient extends BaseClient
{
    protected $timerIds = [];

    public function socketOpen($host, $port)
    {
        $client = $this->socket = new SwooleSocket(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $client->set([
            'open_mqtt_protocol' => true,
        ]);

        $client->on('connect', function () {
            $this->emit('start');
        });

        $client->on("receive", function (SwooleSocket $cli, $data) {
            $this->onReceive($data);
        });

        $client->on("error", function (SwooleSocket $cli) {
            if ($this->debug) {
                echo "error\n";
            }
        });

        $client->on("close", function (SwooleSocket $cli) {
            if ($this->debug) {
                echo "Connection close\n";
            }
        });

        $this->socket->connect($host, $port, 30);
    }

    public function socketSend($data)
    {
        if (!$this->socket || !$this->socket->isConnected()) {
            throw new \Exception("Connection Lost");
        }
        $this->socket->send($data);
    }

    public function socketClose()
    {
        foreach ($this->timerIds as $timerId) {
            Timer::clear($timerId);
        }
        $this->socket->close();
    }

    protected function timerTick($seconds, $callback)
    {
        $this->timerIds[] = Timer::tick($seconds * 1000, $callback);
    }
}
