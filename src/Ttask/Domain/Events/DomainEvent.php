<?php

namespace Ttask\Domain\Events;

/**
 * Class DomainEvent
 * @package Ttask\Domain\Events
 */
class DomainEvent
{

    protected string $name;
    protected string $event_id;
    protected array  $body;

    public function __construct(array $body, string $name, string $id = null)
    {

        $this->event_id = $id ?? uniqid();
        $this->name     = $name;
        $this->body     = $body;
    }

    /**
     * @return string
     */
    public function name():string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function id():string
    {

        return $this->event_id;
    }

    /**
     * @return array
     */
    public function body():array
    {

        return $this->body;
    }
}