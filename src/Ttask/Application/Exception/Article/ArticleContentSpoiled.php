<?php

namespace Ttask\Application\Exception\Article;

use Ttask\Application\Exception\GeneralException;

final class ArticleContentSpoiled extends GeneralException {

    public function __construct()
    {

        parent::__construct(500, "Article was damaged");
    }
}