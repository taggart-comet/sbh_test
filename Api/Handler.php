<?php

namespace Api;

use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

/**
 * Class Handler
 * @package Api
 *  Основной класс работа API
 *  API пока (но можно легко раскрутить) условно 3х уровневое
 *  resource/action или resource/:id/action
 *  Данный класс первично начинает цепочку работы над проверкой и исполнением запроса
 *   - далее передает в `Runner`, который передает в `Checker`
 *      - Этот класс работает с клиентом,
 *      - `Runner` - находит финальный метод,
 *      - `Checker` - проверяет что тип запроса корректен (или что-то еще)
 */
class Handler
{

    // methods
    const METHOD_GET     = 'GET';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_PATCH   = 'PATCH';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';

    //
    const RESOURCE_ID_INT    = ':int';      // resources/int_id
    const RESOURCE_ID_STRING = ':string';   // resources/string_id
    const RESOURCE_ALL       = ':all';      // resources

    const API_PREFIX      = '/api/v1/';
    const ALLOW_RESOURCES = [
          'article',
    ];

    //
    public static function serve(Request $request, Response $response):Response
    {

        //
        $endpoint_split = explode('/', str_replace(self::API_PREFIX, '', $request->getUri()->getPath()));

        // проверяем что запрошеный ресурс валиден
        if (!isset($endpoint_split[0]) || !in_array($endpoint_split[0], self::ALLOW_RESOURCES)) {
            $response_body = self::error(405, "Resource `{$endpoint_split[0]}` was not found");
        } else {

            $resource = $endpoint_split[0];
            unset($endpoint_split[0]);
            $endpoint_split = array_values($endpoint_split);

            // работаем
            $response_body = Runner::work($resource, $endpoint_split, $request);
        }

        // выставляем результат к респонсу
        $sf       = new \Zend\Diactoros\StreamFactory();
        $response = $response->withBody($sf->createStream(json_encode($response_body)));

        // добавляем http-code and returning
        return $response->withStatus($response_body['status_code']);
    }

    // ставит cookies, http-code, и echos ответ клиенту
    public static function output(Response $response):void
    {

        // set headers
        foreach ($response->getHeaders() as $k => $v) {
            header("{$k}: {$v}");
        }

        // we server json only here
        header('Content-Type: application/json');

        // status code
        http_response_code($response->getStatusCode());

//        $special_error_for_test->off();
        // echo
        echo $response->getBody()->getContents();
    }

    // -------------------------------------------------------
    // OK/Error
    // -------------------------------------------------------

    public static function ok(array $response_array = [], int $status_code = 200):array
    {

        return [
              'status'      => 'ok',
              'response'    => $response_array,
              'status_code' => $status_code,
        ];
    }

    public static function error(int $error_code = 405, string $message = '')
    {

        return [
              'status'      => 'error',
              'status_code' => $error_code,
              'message'     => $message,
        ];
    }

    // -------------------------------------------------------
    // Utils
    // -------------------------------------------------------

    public static function isHostAllowed(Request $request):bool
    {

        if (in_array($request->getHeader('host')[0], ALLOWED_HOSTS)) {
            return true;
        }

        return false;
    }

    public static function parseHeaders(array $server_array):array
    {

        $headers = [];
        foreach ($server_array as $key => $value) {
            if (preg_match('/^HTTP_(.*)/', $key, $matches)) {
                $headers[strtolower($matches[1])] = $value;
            }
        }
        return $headers;
    }
}
