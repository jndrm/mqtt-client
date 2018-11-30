<?php

namespace Drmer\Mqtt\Client;

use Drmer\Mqtt\Packet\Protocol\Version;
use React\EventLoop\LoopInterface as Loop;
use React\Socket\ConnectionInterface as Connection;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\TcpConnector;

class ReactClient extends BaseClient
{
    /**
     * @var $loop Loop
     */
    private $loop;
    private $socketConnector;

    public function __construct(Version $version)
    {
        parent::__construct($version);
        $this->loop = LoopFactory::create();
        $this->socketConnector = new TcpConnector($this->loop);
    }

    public function socketOpen($host, $port)
    {
        $promise = $this->socketConnector->connect("tcp://{$host}:{$port}");
        $promise->then(function (Connection $stream) {
            $this->socket = $stream;
            $this->emit('connect');
            $stream->on('data', [$this, 'onData']);
        });
        $this->loop->run();
    }

    public function socketSend($data)
    {
        $this->socket->write($data);
    }

    public function socketClose()
    {
        $this->socket->close();
    }

    public function onData($rawData)
    {
        foreach ($this->getNextPacket($rawData) as $data) {
            $this->onReceive($data);
        }
    }

    public function getNextPacket($remainingData)
    {
        while (isset($remainingData{1})) {
            $remainingLength = ord($remainingData{1});
            $packetLength = 2 + $remainingLength;
            $nextPacketData = substr($remainingData, 0, $packetLength);
            $remainingData = substr($remainingData, $packetLength);

            yield $nextPacketData;
        }
    }

    public function timerTick($seconds, $callback)
    {
        $this->loop->addPeriodicTimer($seconds, $callback);
    }
}
