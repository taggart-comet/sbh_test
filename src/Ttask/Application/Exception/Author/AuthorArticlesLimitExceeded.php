<?php

namespace Ttask\Application\Exception\Author;

use Ttask\Application\Exception\GeneralException;

final class AuthorArticlesLimitExceeded extends GeneralException {

    public function __construct()
    {

        parent::__construct(403, "Too many articles for this author");
    }
}