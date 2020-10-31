<?php

namespace Ttask\Domain\VOs;

use Ttask\Application\Exception\Author\InvalidAuthorId;

// автоинкремент в таблице
final class AuthorId extends IntValueObject {

    /**
     * AuthorId constructor.
     *
     * @param int $author_id
     *
     * @throws InvalidAuthorId
     */
    public function __construct(int $author_id)
    {

        if ($author_id < 1) {
            throw new InvalidAuthorId();
        }

        parent::__construct($author_id);
    }
}