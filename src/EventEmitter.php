<?php

namespace Drmer\Mqtt\Client;

use League\Event\Emitter;

class EventEmitter extends Emitter
{
    public function on($event, $listener, $priority = self::P_NORMAL)
    {
        parent::addListener($event, $listener, $priority);
    }

    public function off($event, $listener)
    {
        parent::removeListener($event, $listener);
    }
}
