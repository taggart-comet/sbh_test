<?php

namespace Ttask\Application\Exception;

final class InvalidEventServiceUsage extends GeneralException {

    public function __construct()
    {

        parent::__construct(500, "Invalid event service usage");
    }
}