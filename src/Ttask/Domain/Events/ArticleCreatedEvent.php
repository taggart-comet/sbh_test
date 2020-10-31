<?php

namespace Ttask\Domain\Events;

final class ArticleCreatedEvent extends DomainEvent {

    const EVENT_NAME = 'ArticleCreatedEvent';

    public function __construct(array $body)
    {

        parent::__construct($body, self::EVENT_NAME);
    }
}
