<?php


require_once __DIR__ . '/start.php';

ini_set('display_errors', 1);

$row = \Ttask\Infrastructure\Persistence\PhpMyOrm\ArticleMain::objects()->get();

print_r($row->getAsArray());
