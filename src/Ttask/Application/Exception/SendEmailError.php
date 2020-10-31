<?php

namespace Ttask\Application\Exception;

final class SendEmailError extends GeneralException {

    public function __construct()
    {

        parent::__construct(500, "Failed to send an email");
    }
}