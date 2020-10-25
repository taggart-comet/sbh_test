<?php

namespace Api\System;

/**
 * Interface StorageInterface
 * @package Api\System
 */
interface StorageInterface
{

    /**
     * @param array $data
     */
    public function setData(array $data);

    /**
     * @return array
     */
    public function getData():array;

    /**
     * @return void
     */
    public function save():void;

    /**
     * @return void
     */
    public function delete():void;
}
