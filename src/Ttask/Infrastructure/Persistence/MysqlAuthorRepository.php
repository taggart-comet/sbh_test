<?php

namespace Ttask\Infrastructure\Persistence;

use PhpMyOrm\sql\DoesNotExist;
use Ttask\Application\Exception\Author\AuthorNotFound;
use Ttask\Domain\Author;
use Ttask\Domain\AuthorFactory;
use Ttask\Domain\Repository\AuthorRepository;
use Ttask\Domain\VOs\AuthorArticleCount;
use Ttask\Domain\VOs\AuthorCreatedAt;
use Ttask\Domain\VOs\AuthorId;
use Ttask\Domain\VOs\AuthorName;
use Ttask\Infrastructure\Persistence\PhpMyOrm\AuthorMain;

/**
 * Class MysqlAuthorRepository
 * @package Ttask\Infrastructure\Persistence
 *
 *          Используем PhpMyOrm
 *          Гидрируем вручную =(
 */
final class MysqlAuthorRepository implements AuthorRepository
{

    /**
     * @param Author $author
     *
     * @throws \PhpMyOrm\IncorrectModelSetup
     */
    public function update(Author $author):void
    {

        AuthorMain::objects()->filter_primary($author->id()->value())->updateMany([
              'author_name'   => $author->name()->value(),
              'created_at'    => $author->createdAt()->value(),
              'article_count' => $author->articleCount()->value(),
        ], 1);
    }

    /**
     * @param AuthorId $author_id
     *
     * @return Author
     * @throws AuthorNotFound
     * @throws \PhpMyOrm\sql\IncorrectFilterParams
     * @throws \Ttask\Application\Exception\Author\AuthorArticlesLimitExceeded
     * @throws \Ttask\Application\Exception\Author\InvalidAuthorId
     * @throws \Ttask\Application\Exception\Author\InvalidAuthorName
     */
    public function get(AuthorId $author_id):Author
    {

        try {
            /** @var AuthorMain $row */
            $row = AuthorMain::objects()->filter_primary($author_id->value())->get();
        } catch (DoesNotExist $e) {
            throw new AuthorNotFound();
        }

        return self::_map($row);
    }

    /**
     * @param AuthorName $author_name
     *  Запрашиваем по имени (индексированному полю `author_name`) автора
     *
     * @return Author
     * @throws AuthorNotFound
     */
    public function getByName(AuthorName $author_name):Author
    {

        try {
            /** @var AuthorMain $row */
            $row = AuthorMain::objects()->filter_equal('author_name', $author_name->value())->get();
        } catch (DoesNotExist $e) {
            throw new AuthorNotFound();
        }

        return self::_map($row);
    }

    public function create(
          AuthorName $author_name,
          AuthorCreatedAt $created_at,
          AuthorArticleCount $article_count
    ):Author {

        $new            = new AuthorMain([
              'author_name'   => $author_name->value(),
              'created_at'    => $created_at->value(),
              'article_count' => $article_count->value(),
        ]);
        $author_id      = $new->save();
        $new->author_id = (int) $author_id;

        return self::_map($new);
    }

    // -------------------------------------------------------
    // Protected
    // -------------------------------------------------------

    protected static function _map(AuthorMain $row):Author
    {

        return AuthorFactory::create(
              $row->author_id,
              $row->author_name,
              $row->article_count,
              $row->created_at
        );
    }
}
