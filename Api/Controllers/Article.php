<?php

namespace Api\Controllers;

use Api\Handler;
use Api\Validator;
use Ttask\Application\Article\ArticleListService;
use Ttask\Application\Article\ArticleSaveDTO;
use Ttask\Application\Article\ArticleSaveService;
use Ttask\Infrastructure\Bus\RabbitMqEventBus;
use Ttask\Infrastructure\Persistence\MysqlArticleRepository;
use Ttask\Infrastructure\Persistence\MysqlAuthorRepository;

class Article extends ControllerInterface
{

    public const ALLOWED_REQUESTS = [
          Handler::RESOURCE_ID_INT,
    ];

    //
    const ALLOWED_ACTIONS = [
          'save' => Handler::METHOD_PUT,
          'list' => Handler::METHOD_GET,
    ];

    public const ALLOWED_ID_ACTIONS = [
//          'get' => Handler::METHOD_GET,
    ];

    // -------------------------------------------------------
    // Direct Actions section
    // -------------------------------------------------------

    //
    public static function save(array $request_data):array
    {

        $title       = Validator::string($request_data, 'title');
        $text        = Validator::string($request_data, 'text');
        $author_name = Validator::string($request_data, 'author_name');

        // NOTE: то что мы здесь вот так создаем service с созданием репозиториев и Bus-ов по сути в каждом
        // NOTE: контроллере не хорошо, конечно стоило сделать через какой-то ControllerParentClass + ServiceBus
        $service = new ArticleSaveService(
              new ArticleSaveDTO($title, $text, $author_name),
              new MysqlArticleRepository(),
              new MysqlAuthorRepository(),
              new RabbitMqEventBus()
        );
        $service->work();

        return Handler::ok([], 201);
    }

    //
    public static function list(array $request_data)
    {

        $limit       = Validator::int($request_data, 'limit', false);
        $offset      = Validator::int($request_data, 'offset', false);

        $service = new ArticleListService(
              $limit ?? 10,
              $offset ?? 0,
              new MysqlArticleRepository()
        );

        return Handler::ok([
              'list' => $service->work()
        ]);
    }

    // -------------------------------------------------------
    // ID Actions section
    // -------------------------------------------------------

}
