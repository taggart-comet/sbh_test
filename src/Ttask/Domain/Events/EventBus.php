<?php

namespace Ttask\Domain\Events;

interface EventBus
{
    public function publish(DomainEvent $event): void;

    public function consume(callable $work, array $extra = []): void;
}
