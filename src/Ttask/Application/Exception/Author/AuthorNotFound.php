<?php

namespace Ttask\Application\Exception\Author;

use Ttask\Application\Exception\GeneralException;

final class AuthorNotFound extends GeneralException {

    public function __construct()
    {

        parent::__construct(400, "Article was not found");
    }
}