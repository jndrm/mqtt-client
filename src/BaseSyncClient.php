<?php

namespace Drmer\Mqtt\Client;

use Drmer\Mqtt\Packet\Protocol\Version;
use Drmer\Mqtt\Packet\Protocol\Version4;
use Drmer\Mqtt\Packet\Connect;
use Drmer\Mqtt\Packet\Publish;
use Drmer\Mqtt\Packet\Disconnect;
use Drmer\Mqtt\Packet\Subscribe;
use Drmer\Mqtt\Packet\Unsubscribe;
use Drmer\Mqtt\Packet\ControlPacket;
use Drmer\Mqtt\Packet\ConnectionOptions;
use Drmer\Mqtt\Packet\Utils\MessageHelper;

abstract class BaseSyncClient {
    protected $socket;

    protected $version;
    protected $messageCounter = 1;

    public $debug = false;

    public abstract function socketOpen($host, $port);
    public abstract function socketSend($data);
    public abstract function socketClose();

    public abstract function isConnected();

    public function __construct(Version $version)
    {
        $this->version = $version;
    }

    public static function v4()
    {
        if (get_called_class() == 'Drmer\Mqtt\Client\BaseSyncClient') {
            throw new \RuntimeException("Error Processing Request");
        }
        return new static(new Version4());
    }

    public function connect($host, $port, $opts=[])
    {
        if (!$this->socketOpen($host, $port)) {
            return false;
        }
        // send connect packet
        $this->sendConnectPacket(new ConnectionOptions($opts));
        return true;
    }

    protected function sendPacket(ControlPacket $packet)
    {
        if ($this->debug) {
            echo "send:\t\t" . get_class($packet) . "\n";
            echo MessageHelper::getReadableByRawString($packet->get());
        }
        return $this->socketSend($packet->get());
    }

    protected function sendConnectPacket(ConnectionOptions $options) {
        $packet = new Connect(
            $this->version,
            $options->username,
            $options->password,
            $options->clientId,
            $options->cleanSession,
            $options->willTopic,
            $options->willMessage,
            $options->willQos,
            $options->willRetain,
            $options->keepAlive
        );
        $this->sendPacket($packet);
    }

    /**
     * @return \React\Promise\Promise
     */
    public function publish($topic, $message, $qos = 0, $dup = false, $retain = false)
    {
        $packet = new Publish($this->version);
        $packet->setTopic($topic);
        $packet->setMessageId($this->messageCounter++);
        $packet->setQos($qos);
        $packet->setDup($dup);
        $packet->setRetain($retain);
        $packet->addRawToPayLoad($message);
        $this->sendPacket($packet);
    }

    public function disconnect()
    {
        $packet = new Disconnect($this->version);
        return $this->sendPacket($packet);
    }

    public function close()
    {
        $this->socketClose();
    }
}
