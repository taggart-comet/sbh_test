<?php

namespace Ttask\Domain;

use Ttask\Domain\VOs\AuthorArticleCount;
use Ttask\Domain\VOs\AuthorCreatedAt;
use Ttask\Domain\VOs\AuthorId;
use Ttask\Domain\VOs\AuthorName;

final class AuthorFactory
{

    /**
     * @param int    $author_id
     * @param string $author_name
     * @param int    $articles_count
     * @param int    $created_at
     *
     * @return Author
     * @throws \Ttask\Application\Exception\Author\AuthorArticlesLimitExceeded
     * @throws \Ttask\Application\Exception\Author\InvalidAuthorId
     * @throws \Ttask\Application\Exception\Author\InvalidAuthorName
     */
    public static function create(int $author_id, string $author_name, int $articles_count, int $created_at):Author
    {

        //
        return new Author(
              new AuthorId($author_id),
              new AuthorName($author_name),
              new AuthorArticleCount($articles_count),
              new AuthorCreatedAt($created_at),
        );
    }
}
