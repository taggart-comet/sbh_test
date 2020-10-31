<?php

namespace Ttask\Application\Exception;

final class NoServiceForEvent extends GeneralException {

    public function __construct()
    {

        parent::__construct(500, "Service for the event, was not registered");
    }
}