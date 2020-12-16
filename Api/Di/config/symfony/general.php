<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $configurator) {

    $services = $configurator->services();

    // basic
    $services->set('event_bus', 'Ttask\Infrastructure\Bus\RabbitMqEventBus');

    // domain-stuff
    $services->set('article_repository', 'Ttask\Infrastructure\Persistence\MysqlArticleRepository');
    $services->set('author_repository', 'Ttask\Infrastructure\Persistence\MysqlAuthorRepository');

    // controllers
    $services->set('article', 'Api\Controllers\Article')
          ->args([service('event_bus'), service('article_repository'), service('author_repository')])
          ->public()
    ;
};

