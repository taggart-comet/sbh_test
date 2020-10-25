<?php

require_once __DIR__ . '/../../../start.php';

// -------------------------------------------------------
// prepare
// -------------------------------------------------------

//
$request = new \Zend\Diactoros\Request(
      $_SERVER['REQUEST_URI'],
      $_SERVER['REQUEST_METHOD'],
      'php://input',
      Api\Handler::parseHeaders($_SERVER)
);

// checking if request comes from the allowed host
if (!\Api\Handler::isHostAllowed($request)) {
    http_response_code(405);
    exit;
}

// NOTE: доп pre-middleware можно вставлять тут

// -------------------------------------------------------
// Исполняем работу
// -------------------------------------------------------
$response = Api\Handler::serve($request, new \Zend\Diactoros\Response());

// NOTE: доп after-middleware можно вставлять тут

// -------------------------------------------------------
// Возвращаем клиенту респонс
// -------------------------------------------------------
Api\Handler::output($response);
