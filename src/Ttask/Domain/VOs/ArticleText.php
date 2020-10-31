<?php

namespace Ttask\Domain\VOs;

use Ttask\Application\Exception\Article\InvalidArticleText;

// можем ограничется например 10000 символов на статью
final class ArticleText extends StringValueObject {
    const MAX_LENGTH = 10000;

    /**
     * ArticleText constructor.
     *
     * @param string $value
     *
     * @throws InvalidArticleText
     */
    public function __construct(string $value)
    {
        if (mb_strlen($value) > self::MAX_LENGTH) {
            throw new InvalidArticleText();
        }

        parent::__construct($value);
    }
}