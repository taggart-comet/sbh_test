<?php

namespace Ttask\Application\Article;

/**
 * Class ArticleSaveDTO
 * @package Blog\Application\Article
 *          Создается в контроллере и передается в Service
 */
final class ArticleSaveDTO
{

    protected string $article_title;
    protected string $article_text;
    protected string $author_name;

    public function __construct(string $article_title, string $article_text, string $author_name)
    {

        $this->article_title = $article_title;
        $this->article_text  = $article_text;
        $this->author_name   = $author_name;
    }

    /**
     * @return string
     */
    public function getArticleText():string
    {

        return $this->article_text;
    }

    /**
     * @return string
     */
    public function getArticleTitle():string
    {

        return $this->article_title;
    }

    /**
     * @return string
     */
    public function getAuthorName():string
    {

        return $this->author_name;
    }
}