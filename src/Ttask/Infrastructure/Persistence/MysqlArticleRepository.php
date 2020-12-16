<?php

namespace Ttask\Infrastructure\Persistence;

use PhpMyOrm\sql\DoesNotExist;
use Ttask\Application\Exception\Article\ArticleContentSpoiled;
use Ttask\Application\Exception\Article\ArticleNotFound;
use Ttask\Domain\Article;
use Ttask\Domain\ArticleFactory;
use Ttask\Domain\VOs\ArticleId;
use Ttask\Domain\Repository\ArticleRepository;
use Ttask\Infrastructure\Persistence\PhpMyOrm\ArticleMain;

/**
 * Class MysqlArticleRepository
 * @package Ttask\Infrastructure\Persistence
 *
 *          Используем PhpMyOrm
 *          Гидрируем вручную =(
 */
final class MysqlArticleRepository implements ArticleRepository
{

    /**
     * @param Article $article
     *
     * @throws \PhpMyOrm\IncorrectModelSetup
     */
    public function save(Article $article):void
    {

        $new_article = new ArticleMain([
              'article_id'      => $article->id()->value(),
              'author_id'       => $article->authorId()->value(),
              'created_at'      => $article->createdAt()->value(),
              'article_content' => [
                    'article_title' => $article->title()->value(),
                    'article_text'  => $article->text()->value(),
              ],
        ]);
        $new_article->saveOrUpdate([
              'article_content' => [
                    'article_title' => $article->title()->value(),
                    'article_text'  => $article->text()->value(),
              ]
        ]);
    }

    /**
     * @param ArticleId $article_id
     *
     * @return Article
     * @throws ArticleContentSpoiled
     * @throws ArticleNotFound
     * @throws \PhpMyOrm\sql\IncorrectFilterParams
     */
    public function get(ArticleId $article_id):Article
    {

        try {
            /** @var ArticleMain $row */
            $row = ArticleMain::objects()->filter_primary($article_id->value())->get();
        } catch (DoesNotExist $e) {
            throw new ArticleNotFound();
        }

        return self::_map($row);
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return Article[]
     * @throws ArticleContentSpoiled
     */
    public function getList(int $limit, int $offset):array
    {
        try {
            $list = ArticleMain::objects()->order_desc('created_at')->getList($limit, $offset);
        } catch (DoesNotExist $e) {
            return [];
        }

        $output = [];

        foreach ($list as $row) {
            $output[] = self::_map($row);
        }

        return $output;
    }

    // -------------------------------------------------------
    // Protected
    // -------------------------------------------------------

    public static function _map(ArticleMain $row):Article {

        $content = $row->article_content;

        if (!isset($content['article_title']) || !isset($content['article_text'])) {
            throw new ArticleContentSpoiled();
        }

        return ArticleFactory::create(
              $row->author_id,
              $content['article_title'],
              $content['article_text'],
              $row->created_at
        );
    }
}
