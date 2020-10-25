<?php

namespace PhpMyOrm\sql;

use PDO;

/**
 * Working directly with database here
 */
class MysqlAdapter
{

    const GLOBAL_KEY = 'php_my_orm_pdo_driver';

    /**
     * Singleton per database
     */
    public static function init(string $db):Mysql
    {

        if (isset($GLOBALS[self::GLOBAL_KEY][$db])) {
            return $GLOBALS[self::GLOBAL_KEY][$db];
        }

        if (!isset($GLOBALS[self::GLOBAL_KEY])) {
            $GLOBALS[self::GLOBAL_KEY] = [];
        }

        $conf = self::_getConf($db);

        # making the dsn string
        $dsn = "mysql:host={$conf['host']}:{$conf['port']};";
        if (!is_null($dsn)) {
            $dsn .= "dbname={$conf['database_name']};";
        }
        $dsn .= "charset=utf8;";

        // connect options
        $options = [
              PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
              PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
              PDO::ATTR_EMULATE_PREPARES   => false,
            # https://stackoverflow.com/questions/10113562/pdo-mysql-use-pdoattr-emulate-prepares-or-not
        ];

        // ssl
        if ($conf['use_ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CIPHER]             = "DHE-RSA-AES256-SHA:AES128-SHA";
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        //
        $GLOBALS[self::GLOBAL_KEY][$db] = new Mysql(
              $dsn,
              $conf["user"],
              $conf["password"],
              $options
        );

        return $GLOBALS[self::GLOBAL_KEY][$db];
    }

    // -------------------------------------------------------
    // UTILS
    // -------------------------------------------------------

    protected static function _getConf($db)
    {

        $conf = require __DIR__ . '/../../conf.php';

        if (!isset($conf['DATABASES']) || count($conf['DATABASES']) < 1) {
            throw new ConfigFormatError("No DATABASES specified");
        }

        if (!isset($conf['DATABASES'][$db])) {
            throw new ConfigFormatError("Database `{$db}` is not specified in conf.php:DATABASES");
        }

        $obligatory_fields = [
              'database_name',
              'user',
              'password',
              'host',
              'port',
              'use_ssl',
        ];

        $db_config = $conf['DATABASES'][$db];

        foreach ($obligatory_fields as $field) {
            if (!isset($db_config[$field])) {
                throw new ConfigFormatError("No `{$field}` specified for `{$db}`");
            }
        }

        return $db_config;
    }
}

class Mysql extends PDO
{

    // -------------------------------------------------------
    // INSERT
    // -------------------------------------------------------

    public function insert(string $query, array $set)
    {

        $this->_query($query, $set);
        return $this->lastInsertId();
    }

    // WARNING this query goes unprepared
    // use with caution
    public function insertMany(string $query)
    {

        $this->query($query);
    }

    // -------------------------------------------------------
    // SELECT
    // -------------------------------------------------------

    public function getOne(string $query, array $set):array
    {

        $stmt = $this->prepare($query);
        $stmt->execute($set);

        $result = $stmt->fetch();
        return is_array($result) ? $result : [];
    }

    public function getMany(string $query, array $set):array
    {

        $stmt = $this->prepare($query);
        $stmt->execute($set);

        $result = $stmt->fetchAll();
        return is_array($result) ? $result : [];
    }

    // -------------------------------------------------------
    // UPDATE & DELETE $ CustomQuery
    // -------------------------------------------------------

    public function executeQuery(string $query, array $set)
    {

        Utils::consoleLog('[MyORM_DEBUG]: ' . $query);

        $this->_query($query, $set);
    }

    // -------------------------------------------------------
    // Utils
    // -------------------------------------------------------

    protected function _query($query, $set)
    {

        Utils::consoleLog('[MyORM_DEBUG]: ' . $query);

        $this->prepare($query)->execute($set);
    }

}

