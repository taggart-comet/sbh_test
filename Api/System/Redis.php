<?php

namespace Api\System;

/**
 * Class Redis
 * @package Api\System
 *          Используем редис для хранения массивов по ID
 */
class Redis implements StorageInterface
{

    protected $_id;
    protected $_source;
    protected $_redis;
    protected $_data = null;

    public function __construct(int $id, string $source = 'default')
    {

        $this->_id     = $id;
        $this->_source = $source;

        //
        $this->_redis = new \Redis();
        $this->_redis->pconnect(REDIS_HOST);
        $this->_redis->auth(REDIS_PASSWORD);
    }

    public function __destruct()
    {

        $this->_redis->close();
    }

    // Singleton
    protected static $_instance = [];

    public static function getInstance(int $id, string $source = 'default'):self
    {
        $key = md5($source . '_' . $id);

        if (!isset(self::$_instance[$key])) {
            self::$_instance[$key] = new self($id, $source);
        }

        return self::$_instance[$key];
    }

    public function setData(array $data)
    {

        $this->_data = $data;
        return $this;
    }

    public function getData():array
    {

        if (!is_null($this->_data)) {
            return $this->_data;
        }

        $raw_data = $this->_redis->get($this->_getKey());
        $ar       = json_decode($raw_data, true);
        if (!is_array($ar)) {
            $ar = [];
        }
        $this->_data = $ar;

        return $this->_data;
    }

    public function save():void
    {

        if (is_null($this->_data)) {
            throw new \RuntimeException('Invalid usage of the method save() in ' . __CLASS__);
        }

        $this->_redis->set($this->_getKey(), json_encode($this->_data));
    }

    public function delete():void
    {
        $this->_redis->del($this->_getKey());
    }

    // -------------------------------------------------------
    // Utils
    // -------------------------------------------------------

    protected function _getKey()
    {

        return md5($this->_source . '_' . $this->_id);
    }
}
