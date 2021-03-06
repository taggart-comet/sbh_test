<?php

namespace Ttask\Application\Exception\Article;

use Ttask\Application\Exception\GeneralException;

final class ArticleTitleDuplicate extends GeneralException {

    public function __construct()
    {

        parent::__construct(400, "Article title duplicate");
    }
}