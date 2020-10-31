<?php

namespace Ttask\Application\Exception\Article;

use Ttask\Application\Exception\GeneralException;

final class InvalidArticleText extends GeneralException {

    public function __construct()
    {

        parent::__construct(400, "Article text is invalid");
    }
}