<?php

namespace Ttask\Application\Article;

use Ttask\Application\ServiceInterface;
use Ttask\Domain\Repository\ArticleRepository;

final class ArticleListService implements ServiceInterface
{

    protected int               $limit;
    protected int               $offset;
    protected ArticleRepository $article_repository;

    public function __construct(int $limit, int $offset, ArticleRepository $article_repository)
    {

        $this->limit              = $limit;
        $this->offset             = $offset;
        $this->article_repository = $article_repository;
    }

    public function work():array
    {
        // where to format for the API
        $list = $this->article_repository->getList($this->limit, $this->offset);

        $output = [];
        foreach ($list as $article) {
            $output[] = $article->getPublicArray();
        }

        return $output;
    }
}
