<?php

namespace Ttask\Domain\Repository;

use Ttask\Domain\VOs\ArticleId;

interface ArticleRepository {

    /**
     * @param Article $article
     */
    public function save(Article $article): void;

    /**
     * @param ArticleId $article_id
     *
     * @return Article
     */
    public function get(ArticleId $article_id): Article;

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return Article[]
     */
    public function getList(int $limit, int $offset): array;
}
