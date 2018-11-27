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
use Drmer\Mqtt\Packet\MessageHelper;
use Drmer\Mqtt\Packet\ControlPacketType;
use Drmer\Mqtt\Packet\ConnectionAck;
use Drmer\Mqtt\Packet\PublishAck;
use Drmer\Mqtt\Packet\PublishReceived;
use Drmer\Mqtt\Packet\PublishRelease;
use Drmer\Mqtt\Packet\PublishComplete;
use League\Event\Emitter as EventEmitter;

abstract class BaseAsyncClient extends EventEmitter {
    protected $socket;

    protected $version;
    protected $messageCounter = 0;

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
        return new static(new Version4());
    }

    public function connect($host, $port, $opts=[])
    {
        $this->addListener('connect', function () use ($opts) {
            $this->sendConnectPacket(new ConnectionOptions($opts));
        });
        return $this->socketOpen($host, $port);
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

    public function publish($topic, $message, $qos = 1, $dup = false, $retain = false)
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

    protected function onReceive($data)
    {
        $controlPacketType = ord($data{0}) >> 4;

        echo "receive data (control is {$controlPacketType}): \n";
        echo MessageHelper::getReadableByRawString($data);

        switch ($controlPacketType) {
            case ControlPacketType::CONNACK:
                $this->emit('connected', ConnectionAck::parse($this->version, $data));
                return;
                break;
            case ControlPacketType::PUBACK:
                $this->emit('puback', PublishAck::parse($this->version, $data));
                break;
            case ControlPacketType::PUBREC:
                $this->onPublishReceive(PublishReceived::parse($this->version, $data));
                break;
            case ControlPacketType::PUBREL:
                $this->onPublishRelease(PublishRelease::parse($this->version, $data));
                break;
            case ControlPacketType::PUBCOMP:
                $this->onPublishComplete(PublishComplete::parse($this->version, $data));
                break;
            default:
                break;
        }
    }

    protected function onPublishReceive($packet)
    {
        $packet = new PublishRelease($this->version);

        $this->sendPacket($packet);
    }

    protected function onPublishComplete($packet)
    {
    }

    public function subscribe($topic, $qos = 0)
    {
        $packet = new Subscribe($this->version);
        $packet->addSubscription($topic, $qos);
        return $this->sendPacket($packet);
    }

    public function unsubscribe($topic)
    {
        $packet = new Unsubscribe($this->version);
        $packet->removeSubscription($topic);
        return $this->sendPacket($packet);
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

    public function on($event, $listener, $priority = self::P_NORMAL)
    {
        parent::addListener($event, $listener, $priority);
    }

    public function off($event, $listener)
    {
        parent::removeListener($event, $listener);
    }
}
