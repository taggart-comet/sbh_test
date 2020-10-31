<?php

namespace Ttask\Application;

use Ttask\Domain\Events\DomainEvent;

/**
 * Interface EventServiceInterface
 * @package Ttask\Application
 */
interface EventServiceInterface {

    /**
     * @param DomainEvent $event
     * @return void
     */
    public function work(DomainEvent $event):void;
}