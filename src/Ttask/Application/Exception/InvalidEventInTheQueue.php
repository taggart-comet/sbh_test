<?php

namespace Ttask\Application\Exception;

final class InvalidEventInTheQueue extends GeneralException {

    public function __construct()
    {

        parent::__construct(500, "Invalid data in the queue message");
    }
}