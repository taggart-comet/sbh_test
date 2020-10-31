<?php

namespace Ttask\Domain;

use Ttask\Domain\VOs\ArticleCreatedAt;
use Ttask\Domain\VOs\ArticleId;
use Ttask\Domain\VOs\ArticleText;
use Ttask\Domain\VOs\ArticleTitle;
use Ttask\Domain\VOs\AuthorId;

final class ArticleFactory
{

    /**
     * @param int    $author_id
     * @param string $article_title
     * @param string $article_text
     * @param int    $created_at
     *
     * @return Article
     * @throws \Ttask\Application\Exception\Article\InvalidArticleText
     * @throws \Ttask\Application\Exception\Article\InvalidArticleTitle
     * @throws \Ttask\Application\Exception\Author\InvalidAuthorId
     */
    public static function create(int $author_id, string $article_title, string $article_text, int $created_at):Article
    {

        //
        $article_title_vo = new ArticleTitle($article_title);

        //
        return new Article(
              new ArticleId($article_title_vo),
              new AuthorId($author_id),
              $article_title_vo,
              new ArticleText($article_text),
              new ArticleCreatedAt($created_at)
        );
    }
}
