<?php

use Api\System\Storage;
use PHPUnit\Framework\TestCase;
use Ttask\Infrastructure\Persistence\PhpMyOrm\ArticleMain;

// ../modules/vendor/bin/phpunit --testsuite main
class IntTestSave extends TestCase
{

    const API_URL = 'https://sbhtest.local/api/v1/';

    protected array $_article       = [
          'title'       => 'Test Title',
          'author_name' => 'Test author',
          'text'        => 'Test Test',
    ];
    protected array $_articles_to_delete = [];

    /** @test */
    // ∞
    public function saveTest()
    {

        // создаем новую статью
        $article_title = md5(mt_rand(0, time()));
        self::assertTrue($this->_save(null, $article_title));

        // проверяем что создалась
        self::assertTrue($this->_isExists($article_title));

        //
        $this->_articles_to_delete[] = $article_title;
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter checkAuthorLimit integration/IntTestSave.php
    public function checkAuthorLimit()
    {

        $author = md5(mt_rand(0, time()));

        // создаем статью с одним автором 2 раза
        for ($i = 0; $i < 2; $i++) {
            $article_title = md5(mt_rand(0, time()));
            self::assertTrue($this->_save($author, $article_title));
            $this->_articles_to_delete[] = $article_title;
        }

        // пробуем создать третий раз и проверяем что не дает
        $article_title = md5(mt_rand(0, time()));
        self::assertFalse($this->_save($author, $article_title));
        $this->_articles_to_delete[] = $article_title;
    }

    // удаляем все что насоздавали
    protected function tearDown()
    {

        foreach ($this->_articles_to_delete as $article_title) {
            ArticleMain::objects()->filter_primary(md5($article_title))->delete();
        }

        $this->_articles_to_delete = [];
    }

    // -------------------------------------------------------
    // Utils
    // -------------------------------------------------------

    protected function _save(string $author = null, string $title = null):bool
    {

        // рандомим автора чтобы не блокировало
        if (is_null($author)) {
            $this->_article['author_name'] = md5(mt_rand(0,time()));
        } else {
            $this->_article['author_name'] = $author;
        }

        //
        if (!is_null($title)) {
            $this->_article['title'] = $title;
        }

        $response = self::_makeRequest(self::API_URL . 'article/save', 'PUT', $this->_article);

        if (isset($response['status']) && $response['status'] == 'ok') {
            return true;
        }

        return false;
    }

    protected function _isExists(string $article_title):bool
    {

        $response = self::_makeRequest(self::API_URL . 'article/list', 'GET');

        if (!isset($response['response']['list'])) {
            return false;
        }

        foreach ($response['response']['list'] as $article) {
            if ($article['title'] == $article_title) {
                return true;
            }
        }

        return false;
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

        return $response;
    }
}