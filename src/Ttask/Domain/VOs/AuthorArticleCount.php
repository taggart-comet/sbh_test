<?php

namespace Ttask\Domain\VOs;

use Ttask\Application\Exception\Author\AuthorArticlesLimitExceeded;

// не может быть больше 2х по задаче от бизнеса
final class AuthorArticleCount extends IntValueObject {

    const ARTICLES_LIMIT = 2;

    /**
     * AuthorArticleCount constructor.
     *
     * @param int $value
     *
     * @throws AuthorArticlesLimitExceeded
     */
    public function __construct(int $value)
    {
        if ($value > self::ARTICLES_LIMIT) {
            throw new AuthorArticlesLimitExceeded();
        }

        parent::__construct($value);
    }
}