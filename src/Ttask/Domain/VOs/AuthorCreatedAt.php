<?php

namespace Ttask\Domain\VOs;

final class AuthorCreatedAt extends IntValueObject
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