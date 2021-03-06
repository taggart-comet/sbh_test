<?php

return [
    // defining databases
      'DATABASES'     => [
            'default'  => [
                  'database_name' => 'sbh_test_system',
                  'user'          => MYSQL_MAIN_USER,
                  'password'      => MYSQL_MAIN_PASS,
                  'host'          => MYSQL_MAIN_HOST,
                  'port'          => MYSQL_MAIN_PORT,
                  'use_ssl'       => MYSQL_MAIN_SSL,
            ],
            'system' => [
                  'database_name' => 'sbh_test_system',
                  'user'          => MYSQL_MAIN_USER,
                  'password'      => MYSQL_MAIN_PASS,
                  'host'          => MYSQL_MAIN_HOST,
                  'port'          => MYSQL_MAIN_PORT,
                  'use_ssl'       => MYSQL_MAIN_SSL,
            ],
            'blog' => [
                  'database_name' => 'sbh_test_blog',
                  'user'          => MYSQL_MAIN_USER,
                  'password'      => MYSQL_MAIN_PASS,
                  'host'          => MYSQL_MAIN_HOST,
                  'port'          => MYSQL_MAIN_PORT,
                  'use_ssl'       => MYSQL_MAIN_SSL,
            ]
      ],

    // defining models
      'MODEL_CLASSES' => [

          'Ttask\\Infrastructure\\Persistence\\PhpMyOrm\\ArticleMain',
          'Ttask\\Infrastructure\\Persistence\\PhpMyOrm\\AuthorMain',

      ],

    // if debug is enabled it will print in console queries on execution
      'SHOW_DEBUG'    => false,
];
