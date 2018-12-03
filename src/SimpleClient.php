<?php

namespace Drmer\Mqtt\Client;

class SimpleClient extends BaseClient
{
    private $errNo;
    private $errStr;

    public function socketOpen($host, $port)
    {
        $this->socket = fsockopen($host, $port, $this->errNo, $this->errStr, 30);
        if ($this->errNo) {
            return false;
        }
        $this->emit('connect');
        return true;
    }

    public function socketSend($data)
    {
        if ($this->errNo) {
            return false;
        }
        fwrite($this->socket, $data);
        return true;
    }

    public function socketClose()
    {
        fclose($this->socket);
    }

    public function timerTick($msec, $callback)
    {
        throw new \RuntimeException("SimpleClient does not support ticker");
    }
}
