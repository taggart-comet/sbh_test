<?php

namespace Ttask\Domain\VOs;

use Ttask\Application\Exception\Article\InvalidArticleText;

// формируем как md5 от тайтла - что дает нам уникальность по тайтлу (требование бизнеса)
final class ArticleId extends StringValueObject {

    public function __construct(ArticleTitle $article_title)
    {

        parent::__construct(md5($article_title));
    }
}