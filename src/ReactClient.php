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

    private $timers = [];

    public function __construct(Version $version)
    {
        parent::__construct($version);
        $this->loop = LoopFactory::create();
        $this->socketConnector = new TcpConnector($this->loop);
    }

    protected function socketOpen($host, $port)
    {
        $promise = $this->socketConnector->connect("tcp://{$host}:{$port}");
        $promise->then(function (Connection $stream) {
            $this->socket = $stream;
            $this->emit('start');
            $stream->on('data', [$this, 'onData']);
        });
        $this->loop->run();
    }

    protected function socketSend($data)
    {
        $this->socket->write($data);
    }

    protected function socketClose()
    {
        foreach ($this->timers as $timer) {
            $this->loop->cancelTimer($timer);
        }
        $this->socket->close();
    }

    public function onData($rawData)
    {
        foreach ($this->getNextPacket($rawData) as $data) {
            $this->onReceive($data);
        }
    }

    protected function getNextPacket($remainingData)
    {
        while (isset($remainingData{1})) {
            $remainingLength = ord($remainingData{1});
            $packetLength = 2 + $remainingLength;
            $nextPacketData = substr($remainingData, 0, $packetLength);
            $remainingData = substr($remainingData, $packetLength);

            yield $nextPacketData;
        }
    }

    protected function timerTick($seconds, $callback)
    {
        $this->timers[] = $this->loop->addPeriodicTimer($seconds, $callback);
    }

    public function getLoop()
    {
        return $this->loop;
    }
}
