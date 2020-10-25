<?php

namespace Api\Models\Storage;

use PhpMyOrm\IntField;
use PhpMyOrm\Model;

/**
 * Храним наши данные: айди и тип хранения
 * @property $id   int
 * @property $type int
 */
class StorageMeta extends Model
{

    public $table_name  = 'storage_meta';
    public $db_name     = 'system';
    public $description = 'Храним наши данные: айди и тип хранения';

    public function fields()
    {

        return [
              'id'   => new IntField(true, true),
              'type' => new IntField(false, false, 0),
        ];
    }
}