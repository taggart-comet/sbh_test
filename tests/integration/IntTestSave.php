<?php

use Api\System\Storage;
use PHPUnit\Framework\TestCase;

// ../modules/vendor/bin/phpunit --testsuite main
class IntTestSave extends TestCase
{

    const API_URL = 'https://sbhtest.local/api/v1/';

    protected $_article       = [
          'title'       => 'Test Title',
          'author_name' => 'Test author',
          'text'        => 'Test Test',
          'type'        => 0,
    ];
    protected $_ids_to_delete = [];

    /** @test */
    // ../modules/vendor/bin/phpunit --filter saveMysql integration/IntTestSave.php
    public function saveMysql()
    {

        // создаем новую статью
        $response = $this->_save(\Api\System\Storage::TYPE_MYSQL);

        self::assertTrue(isset($response['id']));
        $this->_ids_to_delete[$response['id']] = \Api\System\Storage::TYPE_MYSQL;

        // проверяем что создалась
        $get = $this->_get($response['id']);
        self::assertTrue(isset($get['title']));
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter saveFile integration/IntTestSave.php
    public function saveFile()
    {

        // создаем новую статью
        $response = $this->_save(\Api\System\Storage::TYPE_FILE);

        self::assertTrue(isset($response['id']));
        $this->_ids_to_delete[$response['id']] = \Api\System\Storage::TYPE_FILE;

        // проверяем что создалась
        $response = $this->_get($response['id']);
        self::assertTrue(isset($response['title']));
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter saveRedis integration/IntTestSave.php
    public function saveRedis()
    {

        // создаем новую статью
        $response = $this->_save(\Api\System\Storage::TYPE_REDIS);

        self::assertTrue(isset($response['id']));
        $this->_ids_to_delete[$response['id']] = \Api\System\Storage::TYPE_REDIS;

        // проверяем что создалась
        $response = $this->_get($response['id']);
        self::assertTrue(isset($response['title']));
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter checkAuthorLimit integration/IntTestSave.php
    public function checkAuthorLimit()
    {

        $author = md5(mt_rand(0, time()));

        // создаем статью с одним автором 2 раза
        for ($i = 0; $i < 2; $i++) {
            $response = $this->_save(\Api\System\Storage::TYPE_REDIS, $author);

            self::assertTrue(isset($response['id']));
            $this->_ids_to_delete[$response['id']] = \Api\System\Storage::TYPE_REDIS;
        }

        // пробуем создать третий раз и проверяем что не дает
        $response = $this->_save(\Api\System\Storage::TYPE_REDIS, $author);

        self::assertTrue(isset($response['error_code']));
    }

    // удаляем все что насоздавали
    protected function tearDown()
    {

        foreach ($this->_ids_to_delete as $id => $storage_type) {
            Storage::delete($id, $storage_type);
            unset($this->_ids_to_delete[$id]);
        }
    }

    // -------------------------------------------------------
    // Utils
    // -------------------------------------------------------

    protected function _save(int $type, string $author = null):array
    {

        $this->_article['type'] = $type;

        // рандомим автора чтобы не блокировало
        if (is_null($author)) {
            $this->_article['author_name'] = md5(mt_rand(0,time()));
        } else {
            $this->_article['author_name'] = $author;
        }

        return self::_makeRequest(self::API_URL . 'article/save', 'POST', $this->_article);
    }

    protected function _get(int $id):array
    {

        return self::_makeRequest(self::API_URL . 'article/' . $id . '/get', 'GET');
    }

    protected static function _makeRequest(string $url, string $type, array $params = null):array
    {

        $request = new Zend\Diactoros\Request(
              $url,
              $type
        );
        if (!is_null($params)) {
            $sf      = new Zend\Diactoros\StreamFactory();
            $stream  = $sf->createStream(json_encode($params));
            $request = $request->withBody($stream);
        }

        $result = \Api\Handler::serve($request, new \Zend\Diactoros\Response());

        $response = json_decode($result->getBody()->getContents(), true);
        if (!is_array($response)) {
            $response = [];
        }

        return $response['response'] ?? $response;
    }
}