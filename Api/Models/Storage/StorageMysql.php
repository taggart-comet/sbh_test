<?php

namespace Api\Models\Storage;

use PhpMyOrm\IntField;
use PhpMyOrm\JSONField;
use PhpMyOrm\Model;

/**
 *
 * @property $id int
 * @property $content_json array
 */
class StorageMysql extends Model
{

    public $table_name  = 'storage_mysql';
    public $db_name     = 'system';

    public function fields()
    {

        return [
              'id'           => new IntField(true),
              'content_json' => new JSONField(),
        ];
    }
}