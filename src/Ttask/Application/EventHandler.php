<?php

namespace Ttask\Application;

use Ttask\Application\Exception\NoServiceForEvent;
use Ttask\Domain\Events\DomainEvent;

class EventHandler {

    /** @var EventServiceInterface[]  */
    protected array $service_list;

    /**
     * @param string $event_name
     * @param string $service
     */
    public function register(string $event_name, EventServiceInterface $service) {
        $this->service_list[$event_name] = $service;
    }

    public function dispatch(DomainEvent $event) {

        if (!isset($this->service_list[$event->name()])) {
            throw new NoServiceForEvent();
        }

        $this->service_list[$event->name()]->work($event);
    }
}
