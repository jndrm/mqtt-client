<?php

namespace Drmer\Mqtt\Client;

interface ClientInterface
{
    public function socketOpen($host, $port);
    public function socketSend($data);
    public function socketClose();

    public function timerTick($seconds, $callback);

    public function isConnected();
}
