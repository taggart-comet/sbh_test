<?php

namespace Ttask\Domain\VOs;

use Ttask\Application\Exception\Article\InvalidArticleTitle;
use Ttask\Application\Exception\Author\InvalidAuthorName;

final class AuthorName extends StringValueObject
{

    const MAX_LENGTH = 200;
    const MIN_LENGTH = 2;

    /**
     * AuthorName constructor.
     *
     * @param string $value
     *
     * @throws InvalidAuthorName
     */
    public function __construct(string $value)
    {

        if (mb_strlen($value) > self::MAX_LENGTH || mb_strlen($value) < self::MIN_LENGTH) {
            throw new InvalidAuthorName();
        }

        parent::__construct($value);
    }
}