<?php

namespace Ttask\Domain\Repository;

use Ttask\Domain\VOs\AuthorArticleCount;
use Ttask\Domain\VOs\AuthorCreatedAt;
use Ttask\Domain\VOs\AuthorId;
use Ttask\Domain\VOs\AuthorName;

interface AuthorRepository
{

    public function update(Author $author):void;

    public function get(AuthorId $author_id):Author;

    public function getByName(AuthorName $author_name):Author;

    public function create(
          AuthorName $author_name,
          AuthorCreatedAt $created_at,
          AuthorArticleCount $article_count
    ):Author;
}
