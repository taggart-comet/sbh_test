<?php

namespace Api\Controllers;

use Api\Handler;
use Api\Models\Blog\AuthorInfo;
use Api\Models\Storage\StorageMeta;
use Api\Validator;
use PhpMyOrm\sql\DoesNotExist;
use Api\System\Storage;

class Article extends ControllerInterface
{

    // константу создаем здесь - по хорошему надо другое место, но у нас его нет, потому что проект маленький
    const PER_AUTHOR_LIMIT = 2;

    public const ALLOWED_REQUESTS = [
          Handler::RESOURCE_ID_INT,
    ];

    //
    const ALLOWED_ACTIONS = [
          'save' => Handler::METHOD_POST,
          'list' => Handler::METHOD_GET,
    ];

    public const ALLOWED_ID_ACTIONS = [
          'get' => Handler::METHOD_GET,
    ];

    // -------------------------------------------------------
    // Direct Actions section
    // -------------------------------------------------------

    // здесь по хорошему надо делать idempotency_key, но я поленился =)
    public static function save(array $request_data):array
    {

        $text        = Validator::string($request_data, 'text');
        $title       = Validator::string($request_data, 'title');
        $author_name = Validator::string($request_data, 'author_name');
        $type        = Validator::int($request_data, 'type');

        // корректный ли тип
        if (!in_array($type, Storage::ALLOWED_TYPES)) {
            return Handler::error(405, "Invalid type");
        }

        // проверяем нет ли лимита по статьям на автора
        try {
            /** @var AuthorInfo $author_obj */
            $author_obj = AuthorInfo::objects()->filter_equal('author_name', $author_name)->get();
        } catch (DoesNotExist $e) {
            // добавляем автора
            $author_obj = new AuthorInfo(['author_name' => $author_name, 'created_at' => time()]);
        }

        if ($author_obj->article_count >= self::PER_AUTHOR_LIMIT) {
            return Handler::error(403, 'Articles per author limit reached');
        }
        $author_obj->article_count = $author_obj->article_count + 1;
        $author_obj->save();

        // создаем статью
        $article = new \Api\Blog\Article($title, $author_name, $text, $type);

        // сохраняем статью
        $id = Storage::set($type, $article->getAsArray());

        //
        return Handler::ok(array_merge($article->getAsArray(), [
              'id' => $id,
        ]));
    }

    // NOTE: foreach с запросами внутри обсуловлен тем что статьи у нас могут хранится в разных местах (mysql\file\redis)
    // было бы только mysql, очевидно было бы намного быстрее/красивее
    public static function list(array $request_data)
    {

        $author_name = Validator::string($request_data, 'author_name', false);
        $limit       = Validator::int($request_data, 'limit', false);
        $offset      = Validator::int($request_data, 'offset', false);

        // получаем все сохраненные статьи из всех типов хранения
        $list = StorageMeta::objects()->getList($limit ?? 10, $offset ?? 0, true);

        $output = [];
        foreach ($list as $storage_item) {

            $array = Storage::get($storage_item['id'], $storage_item['type']);

            // фильтруем по автору если указан
            if (!is_null($author_name) && $author_name != $array['author_name']) {
                continue;
            }

            //
            $output[] = array_merge($array, ['id' => $storage_item['id']]);
        }

        return Handler::ok([
              'list' => $output,
        ]);
    }

    // -------------------------------------------------------
    // ID Actions section
    // -------------------------------------------------------

    public static function get(array $request_data)
    {
        $article_id = Validator::int($request_data, 'resource_id');

        // определяем где хранится
        try {
            /** @var StorageMeta $storage */
            $storage = StorageMeta::objects()->filter_primary($article_id)->get();
        } catch (DoesNotExist $e) {
            return Handler::error(404, "Resource was not found");
        }

        return Handler::ok(Storage::get($article_id, $storage->type));
    }
}
