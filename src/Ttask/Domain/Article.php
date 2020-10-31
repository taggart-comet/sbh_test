<?php

namespace Ttask\Domain;

use Ttask\Domain\VOs\ArticleCreatedAt;
use Ttask\Domain\VOs\ArticleId;
use Ttask\Domain\VOs\ArticleText;
use Ttask\Domain\VOs\ArticleTitle;
use Ttask\Domain\VOs\AuthorId;

final class Article
{

    protected ArticleId           $article_id;
    protected AuthorId            $author_id;
    protected ArticleTitle        $article_title;
    protected ArticleText         $article_text;
    protected ArticleCreatedAt    $article_created_at;

    public function __construct(
          ArticleId $article_id,
          AuthorId $author_id,
          ArticleTitle $article_title,
          ArticleText $article_text,
          ArticleCreatedAt $article_created_at
    ) {

        $this->article_id         = $article_id;
        $this->author_id          = $author_id;
        $this->article_title      = $article_title;
        $this->article_text       = $article_text;
        $this->article_created_at = $article_created_at;
    }

    /**
     * @return ArticleId
     */
    public function id():ArticleId
    {

        return $this->article_id;
    }

    /**
     * @return AuthorId
     */
    public function authorId():AuthorId
    {

        return $this->author_id;
    }

    /**
     * @return ArticleTitle
     */
    public function title():ArticleTitle
    {

        return $this->article_title;
    }

    /**
     * @return ArticleText
     */
    public function text():ArticleText
    {

        return $this->article_text;
    }

    /**
     * @return ArticleCreatedAt
     */
    public function createdAt():ArticleCreatedAt
    {

        return $this->article_created_at;
    }

    /**
     * @param ArticleText $new_next
     */
    public function updateText(ArticleText $new_next):void
    {

        $this->article_text = $new_next;
    }

    /**
     * Для отображения в апи
     * @return array
     */
    public function getPublicArray():array
    {

        return [
              'article_id' => $this->article_id->value(),
              'author_id'  => $this->author_id->value(),
              'created_at' => $this->article_created_at->value(),
              'title'      => $this->article_title->value(),
              'text'       => $this->article_text->value(),
        ];
    }

}