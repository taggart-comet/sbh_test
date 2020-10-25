<?php

namespace Api\System;

use Api\Models\Storage\StorageMeta;
use Api\Models\Storage\StorageMysql;
use http\Exception\RuntimeException;

/**
 * Class Storage
 * @package System
 *
 *          Сохраняем обьект из контроллера в это хранилище
 *          с параметрами где шина должна хранить объект
 *
 *          Так же при получении объекта обращаемся в хранилище с указанием типа хранения
 *
 *          Получает массив на вход, в виде json сохраняет его в любое хранилище, при обращении отдает сразу массив
 */
class Storage
{

    const TYPE_MYSQL = 0;
    const TYPE_FILE  = 1;
    const TYPE_REDIS = 2;

    const ALLOWED_TYPES = [
          self::TYPE_MYSQL,
          self::TYPE_FILE,
          self::TYPE_REDIS,
    ];

    public static function set(int $storage_type, array $data):int
    {

        // создаем объект хранилища
        $new_id = new StorageMeta(['type' => $storage_type]);
        $id     = $new_id->save();

        // сохраняем объект по айди в нужный тип хранения
        switch ($storage_type) {
            case self::TYPE_MYSQL:
                $new_mysql = new StorageMysql(['id' => $id, 'content_json' => $data]);
                $new_mysql->save();
                break;
            case self::TYPE_FILE:
                $new_file = new File($id);
                $new_file->setData($data);
                $new_file->save();
                break;
            case self::TYPE_REDIS:
                Redis::getInstance($id)->setData($data)->save();
                break;
            default:
                throw new RuntimeException("Invalid storage type: [{$storage_type}]");
        }

        return $id;
    }

    public static function get(int $id, int $storage_type):array
    {

        switch ($storage_type) {
            case self::TYPE_MYSQL:
                /** @var StorageMysql $mysql */
                $mysql = StorageMysql::objects()->filter_primary($id)->get();
                return $mysql->content_json;
            case self::TYPE_FILE:
                $file = new File($id);
                return $file->getData();
            case self::TYPE_REDIS:
                return Redis::getInstance($id)->getData();
            default:
                throw new RuntimeException("Invalid storage type: [{$storage_type}]");
        }
    }

    public static function delete(int $id, int $storage_type) {

        switch ($storage_type) {
            case self::TYPE_MYSQL:
                /** @var StorageMysql $mysql */
                StorageMysql::objects()->filter_primary($id)->delete();
                break;
            case self::TYPE_FILE:
                $file = new File($id);
                $file->delete();
                break;
            case self::TYPE_REDIS:
                Redis::getInstance($id)->delete();
                break;
            default:
                throw new RuntimeException("Invalid storage type: [{$storage_type}]");
        }

        //
        StorageMeta::objects()->filter_primary($id)->delete();
    }

}
