<?php
return [

      'services' => [

          # basic
            'event_bus'          => [
                  'class' => 'Ttask\Infrastructure\Bus\RabbitMqEventBus',
            ],

          # domain-stuff
            'article_repository' => [

                  'class' => 'Ttask\Infrastructure\Persistence\MysqlArticleRepository',
            ],
            'author_repository'  => [

                  'class' => 'Ttask\Infrastructure\Persistence\MysqlAuthorRepository',
            ],

          # controllers
            'article'            => [

                  'class'     => 'Api\Controllers\Article',
                  'arguments' => ['@event_bus', '@article_repository', '@author_repository'],
            ],
      ],
];
