<?php

namespace Ttask\Infrastructure\Persistence\PhpMyOrm;

use Ttask\Domain\VOs\ArticleTitle;
use PhpMyOrm\CharField;
use PhpMyOrm\IntField;
use PhpMyOrm\JSONField;
use PhpMyOrm\Model;
use Ttask\Domain\VOs\AuthorName;

/**
 *
 * @property $author_id int
 * @property $author_name string
 * @property $created_at int
 * @property $article_count int
 */
final class AuthorMain extends Model
{

    public string $table_name = 'author_main';
    public string $db_name    = 'blog';

    public function fields()
    {

        return [
              'author_id'     => new IntField(true, true),
              'author_name'    => new CharField(AuthorName::MAX_LENGTH),
              'created_at'    => new IntField(),
              'article_count' => new IntField(),
        ];
    }

    public function indexes()
    {

        return [
              ['author_name']
        ];
    }
}
