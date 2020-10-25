<?php

namespace Api\Models\Blog;

use PhpMyOrm\CharField;
use PhpMyOrm\IntField;
use PhpMyOrm\Model;

/**
 * Информация об авторах статей, заносится при первой статье
 * @property $author_id     int
 * @property $author_name   string
 * @property $created_at    int
 * @property $article_count int
 */
class AuthorInfo extends Model
{

    public $table_name  = 'author_info';
    public $db_name     = 'blog';
    public $description = 'Информация об авторах статей, заносится при первой статье';

    public function fields()
    {

        return [
              'author_id'     => new IntField(true, true),
              'author_name'   => new CharField(100),
              'created_at'    => new IntField(false, false, 0),
              'article_count' => new IntField(false, false, 0),
        ];
    }

    public function indexes()
    {

        return [
              ['author_name'],
        ];
    }
}
