<?php

namespace Api\System;

/**
 * Class File
 * @package Api\System
 *
 *          Очень простой хранитель массива в файлах
 *          получает и отдает массив, хранит в файлах json
 *          используем file_get_contents, потому что надо всегда сразу целиком файл
 */
class File implements StorageInterface
{

    const PATH_PREFIX = __DIR__ . '/../../files/';

    protected $_id;
    protected $_file_path;
    protected $_file_data = null;

    public function __construct(int $id)
    {

        $this->_id        = $id;
        $this->_file_path = self::_getFilePath($this->_id);
    }

    /**
     * @param array $data
     */
    public function setData(array $data):void
    {

        $this->_file_data = $data;
    }

    /**
     * @return array
     */
    public function getData():array
    {

        if (!is_null($this->_file_data)) {
            return $this->_file_data;
        }

        if (!file_exists($this->_file_path)) {
            throw new \RuntimeException("File [{$this->_id}] was not found");
        }

        // reading the file
        $raw_data = file_get_contents($this->_file_path);

        $ar = json_decode($raw_data, true);
        if (!is_array($ar)) {
            $ar = [];
        }
        $this->_file_data = $ar;

        return $this->_file_data;
    }

    public function save():void
    {

        if (is_null($this->_file_data)) {
            throw new \RuntimeException('Invalid usage of the method save() in ' . __CLASS__);
        }

        // checking if dir exist
        if (!is_dir(self::PATH_PREFIX)) {
            throw new \RuntimeException("No directory for Files");
        }

        //
        file_put_contents($this->_file_path, json_encode($this->_file_data));
    }

    public function delete():void
    {
        unlink(self::_getFilePath($this->_id));
    }

    // -------------------------------------------------------
    // Utils
    // -------------------------------------------------------

    protected static function _getFilePath(int $id)
    {

        return self::PATH_PREFIX . md5($id) . '.system.file.json';
    }


}
