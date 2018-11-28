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
use Drmer\Mqtt\Packet\Utils\Parser;
use Drmer\Mqtt\Packet\ControlPacketType;
use Drmer\Mqtt\Packet\ConnectionAck;
use Drmer\Mqtt\Packet\PingRequest;
use Drmer\Mqtt\Packet\PublishRelease;
use Drmer\Mqtt\Packet\PublishReceived;
use Drmer\Mqtt\Packet\PublishComplete;
use League\Event\Emitter as EventEmitter;

abstract class BaseClient extends EventEmitter {
    protected $socket;

    protected $version;
    protected $messageCounter = 0;

    public $debug = false;

    protected $connectOptions = null;

    protected abstract function socketOpen($host, $port);
    protected abstract function socketSend($data);
    protected abstract function socketClose();
    protected abstract function timerTick($msec, $callback);

    public abstract function isConnected();

    public function __construct(Version $version)
    {
        $this->version = $version;
    }

    public static function v4()
    {
        if (get_called_class() == 'Drmer\Mqtt\Client\BaseClient') {
            throw new \RuntimeException("Could not instance from BaseClient");
        }
        return new static(new Version4());
    }

    public function connect($host, $port, $opts=[])
    {
        $this->connectOptions = new ConnectionOptions($opts);

        $this->on('connect', [$this, 'onConnect']);

        return $this->socketOpen($host, $port);
    }

    public function onConnect()
    {
        $packet = new Connect($this->connectOptions);
        $this->sendPacket($packet);
    }

    protected function sendPacket(ControlPacket $packet)
    {
        if ($this->debug) {
            echo "send:\t\t" . get_class($packet) . "\n";
            echo MessageHelper::getReadableByRawString($packet->get());
        }
        return $this->socketSend($packet->get());
    }

    public function publish($topic, $message, $qos = 1, $dup = false, $retain = false)
    {
        $packet = new Publish($this->version);
        $packet->setTopic($topic);
        $packet->setIdentifier($this->messageCounter++);
        $packet->setQos($qos);
        $packet->setDup($dup);
        $packet->setRetain($retain);
        $packet->addRawToPayLoad($message);
        $this->sendPacket($packet);
    }

    public function subscribe($topic, $qos = 0)
    {
        $packet = new Subscribe();
        $packet->addSubscription($topic, $qos);
        $packet->setIdentifier($this->messageCounter++);
        return $this->sendPacket($packet);
    }

    public function unsubscribe($topic)
    {
        $packet = new Unsubscribe();
        $packet->removeSubscription($topic);
        return $this->sendPacket($packet);
    }

    public function disconnect()
    {
        $packet = new Disconnect();
        return $this->sendPacket($packet);
    }

    public function close()
    {
        $this->socketClose();
    }

    protected function onReceive($data)
    {
        $controlType = ord($data{0}) >> 4;
        if ($this->debug) {
            $cmd = Parser::getCmd($controlType);
            echo "receive data ($cmd): \n";
            echo MessageHelper::getReadableByRawString($data);
        }

        $packet = Parser::parse($data);
        switch ($controlType) {
            case ControlPacketType::CONNACK:
                $this->onConnected($packet);
                break;
            case ControlPacketType::SUBACK:
                $this->onSubscribeAck($packet);
                break;
            case ControlPacketType::PUBLISH:
                $this->onMessage($packet);
                break;
            case ControlPacketType::PUBACK:
                $this->onPublichAck($packet);
                break;
            case ControlPacketType::PUBREC:
                $this->onPublishReceived($packet);
                break;
            case ControlPacketType::PUBREL:
                $this->onPublishReleased($packet);
                break;
            case ControlPacketType::PUBCOMP:
                $this->onPublishComplete($packet);
                break;
            default:
                break;
        }
    }

    public function onConnected($packet)
    {
        $this->emit('connected', $packet);
        if (($keepAlive = $this->connectOptions->keepAlive) > 0) {
            $this->timerTick($keepAlive / 2, function() {
                $this->sendPacket(new PingRequest());
            });
        }
    }

    public function onSubscribeAck($packet)
    {
    }

    public function onMessage($packet)
    {
        $this->emit('message', $packet);

        $receivedPacket = new PublishReceived();
        $receivedPacket->setIdentifier($packet->getIdentifier());
        $this->sendPacket($receivedPacket);
    }

    public function onPublichAck($packet)
    {
    }

    protected function onPublishReceived($packet)
    {
        $releasePacket = new PublishRelease();
        $releasePacket->setIdentifier($packet->getIdentifier());
        $this->sendPacket($releasePacket);
    }


    public function onPublishReleased($packet)
    {
        $completePacket = new PublishComplete();
        $completePacket->setIdentifier($packet->getIdentifier());
        $this->sendPacket($completePacket);
    }

    protected function onPublishComplete($packet)
    {
        if ($this->debug) {
            echo "public complete \n";
        }
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
