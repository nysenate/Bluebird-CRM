<?php

namespace Lurker\EventDispatcher;

/**
 * Interface EventDispatcherInterface
 * @package Lurker
 *
 * This is a strict subset of Symfony 2/3 "EventDispatcherInterface".
 */
interface EventDispatcherInterface
{
    public function dispatch($eventName, $event = null);

    public function addListener($eventName, $listener);
}
