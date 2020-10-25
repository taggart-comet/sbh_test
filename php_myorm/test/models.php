<?php

namespace PhpMyOrm\test;

use PhpMyOrm as db;

require_once __DIR__ . '/../init.php';

/**
 * TestModel: This table is for tests
 * @property $id         int
 * @property $status     int
 * @property $date_added int
 * @property $content    string
 * @property $extra      array
 */
class TestModel extends db\Model
{

    public $table_name  = 'php_myorm_test_table';
    public $description = 'This table is for tests';

    public function fields()
    {

        return [
              'id'         => new db\IntField(true, true),
              'status'     => new db\TinyIntField(),
              'date_added' => new db\IntField(),
              'content'    => new db\CharField(200),
              'extra'      => new db\JSONField(),
        ];
    }

    public function indexes()
    {

        return [
              ['status'],
        ];
    }
}

/**
 *This table is for tests. With autoincrement field
 * @property $status     int
 * @property $date_added int
 * @property $content    string
 * @property $extra      array
 * @property $id         int
 */
class TestModelAuto extends db\Model
{

    public $table_name  = 'php_myorm_test_table_auto';
    public $db_name     = 'default_2';
    public $description = 'This table is for tests. With autoincrement field';

    public function fields()
    {

        return [
              'status'     => new db\TinyIntField(),
              'date_added' => new db\IntField(),
              'content'    => new db\TextFieldField(),
              'extra'      => new db\JSONField(),
        ];
    }

    public function indexes()
    {

        return [
              ['status', 'date_added'],
        ];
    }
}

/**
 * This table is for tests, with multiple primary keys
 * @property $comment_id int
 * @property $article_id string
 * @property $status     int
 * @property $date_added int
 * @property $content    string
 * @property $extra      array
 */
class TestModelMultiPrimary extends db\Model
{

    public $table_name  = 'php_myorm_test_table_multi';
    public $description = 'This table is for tests, with multiple primary keys';

    public function fields()
    {

        return [
              'comment_id' => new db\IntField(true),
              'article_id' => new db\CharField(100, true),
              'status'     => new db\TinyIntField(),
              'date_added' => new db\IntField(),
              'content'    => new db\CharField(1000),
              'extra'      => new db\JSONField(),
        ];
    }

    public function indexes()
    {

        return [
              ['status'],
              ['status', 'date_added'],
        ];
    }
}

