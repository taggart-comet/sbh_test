<?php

namespace Ttask\Domain;

use Ttask\Domain\VOs\AuthorArticleCount;
use Ttask\Domain\VOs\AuthorCreatedAt;
use Ttask\Domain\VOs\AuthorId;
use Ttask\Domain\VOs\AuthorName;

// NOTE: не более 2 статей на автора по имени
final class Author
{

    protected AuthorId                  $author_id;
    protected AuthorName                $author_name;
    protected AuthorArticleCount        $article_count;
    protected AuthorCreatedAt           $created_at;

    /**
     * Author constructor.
     *
     * @param AuthorId           $author_id
     * @param AuthorName         $author_name
     * @param AuthorArticleCount $article_count
     * @param AuthorCreatedAt    $created_at
     */
    public function __construct(
          AuthorId $author_id,
          AuthorName $author_name,
          AuthorArticleCount $article_count,
          AuthorCreatedAt $created_at
    ) {

        $this->author_id     = $author_id;
        $this->author_name   = $author_name;
        $this->article_count = $article_count;
        $this->created_at    = $created_at;
    }

    /**
     * @return AuthorId
     */
    public function id():AuthorId
    {

        return $this->author_id;
    }

    /**
     * @return AuthorName
     */
    public function name():AuthorName
    {

        return $this->author_name;
    }

    /**
     * @return AuthorArticleCount
     */
    public function articleCount():AuthorArticleCount
    {

        return $this->article_count;
    }

    /**
     * @return AuthorCreatedAt
     */
    public function createdAt():AuthorCreatedAt
    {

        return $this->created_at;
    }

    /**
     * @throws \Ttask\Application\Exception\Author\AuthorArticlesLimitExceeded
     */
    public function incrementArticleCount() {

        $this->article_count = new AuthorArticleCount($this->article_count->value()+1);
    }
}
