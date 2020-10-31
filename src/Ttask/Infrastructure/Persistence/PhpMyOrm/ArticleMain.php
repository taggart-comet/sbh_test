<?php

namespace Ttask\Infrastructure\Persistence\PhpMyOrm;

use Ttask\Domain\VOs\ArticleTitle;
use PhpMyOrm\CharField;
use PhpMyOrm\IntField;
use PhpMyOrm\JSONField;
use PhpMyOrm\Model;

/**
 *
 * @property $article_id string
 * @property $author_id int
 * @property $created_at int
 * @property $article_content array
 */
final class ArticleMain extends Model
{

    public string $table_name = 'article_main';
    public string $db_name    = 'blog';

    public function fields()
    {

        return [
              'article_id'      => new CharField(ArticleTitle::MAX_LENGTH, true),
              'author_id'       => new IntField(),
              'created_at'      => new IntField(),
              'article_content' => new JSONField('Should contain title and text of the article'),
        ];
    }

    public function indexes()
    {

        return [
              ['author_id']
        ];
    }
}
