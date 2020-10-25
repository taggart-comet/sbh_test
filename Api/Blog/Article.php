<?php

namespace Api\Blog;

class Article
{

    public $author_name;
    public $text;
    public $title;
    public $type;
    public $created_at;

    protected $_article_array = [];

    public function __construct(string $title, string $author_name, string $text, int $type, int $created_at = null)
    {

        $this->author_name = $author_name;
        $this->text        = $text;
        $this->title       = $title;
        $this->type        = $type;
        $this->created_at  = is_null($created_at) ?? time();

        $this->_article_array = [
              'title'       => $this->title,
              'author_name' => $this->author_name,
              'text'        => $this->text,
              'type'        => $this->type,
              'created_at'  => $this->created_at,
        ];
    }

    public function getAsJson()
    {

        return json_encode($this->_article_array);
    }

    public function getAsArray()
    {

        return $this->_article_array;
    }

    // -------------------------------------------------------
    // STATIC
    // -------------------------------------------------------

    public static function createFromArray(array $array):Article
    {

        $values = [
              'title',
              'author_name',
              'text',
              'type',
              'created_at',
        ];

        foreach ($values as $key) {
            if (!isset($array[$key])) {
                throw new \RuntimeException('Invalid array format passed to ' . __CLASS__);
            }
        }

        return new self(
              $array['title'],
              $array['author_name'],
              $array['text'],
              $array['type'],
              $array['created_at']
        );
    }
}