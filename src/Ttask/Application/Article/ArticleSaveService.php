<?php

namespace Ttask\Application\Article;

use Ttask\Application\Exception\Article\ArticleNotFound;
use Ttask\Application\Exception\Article\ArticleTitleDuplicate;
use Ttask\Application\Exception\Author\AuthorNotFound;
use Ttask\Application\ServiceInterface;
use Ttask\Domain\ArticleFactory;
use Ttask\Domain\Repository\ArticleRepository;
use Ttask\Domain\Repository\AuthorRepository;
use Ttask\Domain\Events\ArticleCreatedEvent;
use Ttask\Domain\Events\EventBus;
use Ttask\Domain\VOs\ArticleId;
use Ttask\Domain\VOs\ArticleText;
use Ttask\Domain\VOs\ArticleTitle;
use Ttask\Domain\VOs\AuthorArticleCount;
use Ttask\Domain\VOs\AuthorCreatedAt;
use Ttask\Domain\VOs\AuthorName;

/**
 * Class ArticleSaveService
 * @package Ttask\Application\Article
 *
 *          Как минимум EventBus должен быть вынесен в ServiceParent
 */
final class ArticleSaveService implements ServiceInterface
{

    protected ArticleSaveDTO    $article_save_dto;
    protected ArticleRepository $article_repository;
    protected AuthorRepository  $author_repository;
    protected EventBus          $event_bus;

    public function __construct(
          ArticleSaveDTO $dto,
          ArticleRepository $article_repository,
          AuthorRepository $author_repository,
          EventBus $event_bus
    ) {

        $this->article_save_dto   = $dto;
        $this->article_repository = $article_repository;
        $this->author_repository  = $author_repository;
        $this->event_bus          = $event_bus;
    }

    public function work()
    {

        // проверяем есть ли такой автор уже
        try {
            $author = $this->author_repository->getByName(new AuthorName($this->article_save_dto->getAuthorName()));
        } catch (AuthorNotFound $e) {

            // создаем нового автора
            $author = $this->author_repository->create(
                  new AuthorName($this->article_save_dto->getAuthorName()),
                  new AuthorCreatedAt(),
                  new AuthorArticleCount(0)
            );
        }

        try {
            // -------------------------------------------------------
            // Обновляем старую статью
            // -------------------------------------------------------
            $article = $this->article_repository->get(new ArticleId(new ArticleTitle($this->article_save_dto->getArticleTitle())));

            // совпадает тайтл но другой автор
            if ($article->authorId()->value() != $author->id()->value()) {
                throw new ArticleTitleDuplicate();
            }

            // обновляем текст статьи
            $article->updateText(new ArticleText($this->article_save_dto->getArticleText()));
        } catch (ArticleNotFound $e) {
            // -------------------------------------------------------
            // Создаем новую статью
            // -------------------------------------------------------
            $article = ArticleFactory::create(
                  $author->id()->value(),
                  $this->article_save_dto->getArticleTitle(),
                  $this->article_save_dto->getArticleText(),
                  time()
            );

            // проверка на лимит статей на автора
            $author->incrementArticleCount();
        }

        // сохраняем обноваления
        $this->article_repository->save($article);
        $this->author_repository->update($author);

        // создаем событие о том что новая статья создана (или обновлена) - для отправки email
        $this->event_bus->publish(new ArticleCreatedEvent($article->getPublicArray()));
    }
}
