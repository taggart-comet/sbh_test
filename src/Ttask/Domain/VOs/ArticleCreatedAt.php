<?php

namespace Ttask\Domain\VOs;

use Ttask\Application\Exception\Article\InvalidArticleText;

final class ArticleCreatedAt extends IntValueObject
{

    /**
     * ArticleCreatedAt constructor.
     *
     * @param int|null $value
     */
    public function __construct(int $value = null)
    {

        if (is_null($value)) {
            $value = time();
        }

        parent::__construct($value);
    }
}