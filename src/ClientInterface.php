<?php

namespace Drmer\Mqtt\Client;

interface ClientInterface {
    function socketOpen($host, $port);
    function socketSend($data);
    function socketClose();
    function timerTick($msec, $callback);

    function isConnected();
}
