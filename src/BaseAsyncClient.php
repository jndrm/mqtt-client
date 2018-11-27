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
use Drmer\Mqtt\Packet\PublishComplete;
use League\Event\Emitter as EventEmitter;

abstract class BaseAsyncClient extends EventEmitter {
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
        if (get_called_class() == 'Drmer\Mqtt\Client\BaseAsyncClient') {
            throw new \RuntimeException("Error Processing Request");
        }
        return new static(new Version4());
    }

    public function connect($host, $port, $opts=[])
    {
        $this->connectOptions = new ConnectionOptions($opts);

        $this->on('connect', [$this, 'onConnect']);

        $this->on('connected', [$this, 'onConnected']);

        return $this->socketOpen($host, $port);
    }

    public function onConnect()
    {
        $packet = new Connect($this->connectOptions);
        $this->sendPacket($packet);
    }

    public function onConnected()
    {
        if (($keepAlive = $this->connectOptions->keepAlive) > 0) {
            $this->timerTick($keepAlive / 2, function() {
                $this->sendPacket(new PingRequest());
            });
        }
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
        $packet = new Connect($options);
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
        $controlType = ord($data{0}) >> 4;

        if ($this->debug) {
            $cmd = Parser::getCmd($controlType);
            echo "receive data ($cmd): \n";
            echo MessageHelper::getReadableByRawString($data);
        }

        $packet = Parser::parse($data);
        switch ($controlType) {
            case ControlPacketType::CONNACK:
                $this->emit('connected', $packet);
                break;
            case ControlPacketType::PUBACK:
                $this->onPublichAck($packet);
                break;
            case ControlPacketType::PUBREC:
                $this->onPublishReceive($packet);
                break;
            case ControlPacketType::PUBREL:
                $this->onPublishRelease($packet);
                break;
            case ControlPacketType::PUBCOMP:
                $this->onPublishComplete($packet);
                break;
            default:
                break;
        }
    }

    public function onPublichAck($packet)
    {
    }

    protected function onPublishReceive($packet)
    {
        $packet = new PublishRelease();

        $this->sendPacket($packet);
    }

    protected function onPublishComplete($packet)
    {
        if ($this->debug) {
            echo "public complete \n";
        }
    }

    public function subscribe($topic, $qos = 0)
    {
        $packet = new Subscribe();
        $packet->addSubscription($topic, $qos);
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

    public function on($event, $listener, $priority = self::P_NORMAL)
    {
        parent::addListener($event, $listener, $priority);
    }

    public function off($event, $listener)
    {
        parent::removeListener($event, $listener);
    }
}
