<?php

namespace Lurker\EventDispatcher;

/**
 * Class EventDispatcher
 * @package Lurker
 *
 * This EventDispatcher supports a strict subset of the contract in
 * Symfony (v2/3) EventDispatcher.
 */
class EventDispatcher implements EventDispatcherInterface
{

    /**
     * @var array
     *
     * $listeners['event-name'][$int] = $callable;
     */
    protected $listeners = [];

    public function addListener($eventName, $listener)
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch($eventName, $event = null)
    {
        $listeners = isset($this->listeners[$eventName]) ? $this->listeners[$eventName] : [];
        foreach ($listeners as $listener) {
            \call_user_func($listener, $event, $eventName, $this);
        }
    }
}
