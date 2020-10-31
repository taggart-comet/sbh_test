<?php

namespace Ttask\Domain\VOs;

abstract class IntValueObject
{
    protected int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function value():int
    {
        return $this->value;
    }
}
