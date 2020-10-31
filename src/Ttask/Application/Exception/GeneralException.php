<?php

namespace Ttask\Application\Exception;

abstract class GeneralException extends \Exception {

    public function __construct(int $code = 405, string $message = "")
    {
        parent::__construct($message, $code);
    }
}