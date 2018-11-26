<?php

namespace Drmer\Mqtt\Client;

class SimpleClient extends BaseSyncClient {
    private $errNo;
    private $errStr;

    public function socketOpen($host, $port)
    {
        $this->socket = fsockopen($host, $port, $this->errNo, $this->errStr, 30);
        if ($this->errNo) {
            return false;
        }
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

    public function isConnected()
    {
        return $this->socket && !$this->errNo;
    }
}
