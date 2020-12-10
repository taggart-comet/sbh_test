<?php

namespace Api\Controllers;

use Api\Handler;
use Api\Validator;
use Ttask\Application\Article\ArticleListService;
use Ttask\Application\Article\ArticleSaveDTO;
use Ttask\Application\Article\ArticleSaveService;
use Ttask\Domain\Repository\ArticleRepository;
use Ttask\Domain\Repository\AuthorRepository;
use Ttask\Domain\Events\EventBus;
use Ttask\Infrastructure\Bus\RabbitMqEventBus;
use Ttask\Infrastructure\Persistence\MysqlArticleRepository;
use Ttask\Infrastructure\Persistence\MysqlAuthorRepository;

class Article extends AbstractController
{

    protected ArticleRepository $article_repository;
    protected AuthorRepository  $author_repository;

    public function __construct(
          EventBus $event_bus,
          ArticleRepository $article_repository,
          AuthorRepository $author_repository
    ) {

        $this->article_repository = $article_repository;
        $this->author_repository  = $author_repository;

        parent::__construct($event_bus);
    }

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
    public function save(array $request_data):array
    {

        $title       = Validator::string($request_data, 'title');
        $text        = Validator::string($request_data, 'text');
        $author_name = Validator::string($request_data, 'author_name');

        //
        $service = new ArticleSaveService(
              new ArticleSaveDTO($title, $text, $author_name),
              $this->article_repository,
              $this->author_repository,
              $this->event_bus
        );
        $service->work();

        return Handler::ok([], 201);
    }

    //
    public function list(array $request_data)
    {

        $limit  = Validator::int($request_data, 'limit', false);
        $offset = Validator::int($request_data, 'offset', false);

        $service = new ArticleListService(
              $limit ?? 10,
              $offset ?? 0,
              $this->article_repository
        );

        return Handler::ok([
              'list' => $service->work(),
        ]);
    }

    // -------------------------------------------------------
    // ID Actions section
    // -------------------------------------------------------

}
