<?php

namespace PhpMyOrm\migrate;

use Exception;
use Throwable;

class ConfigFormatError extends Exception
{

}

class TableStructureException extends Exception
{

}

class NeedToMigrateException extends Exception
{

    public $migrations = [];
    public $warnings   = [];

    public function __construct($migrations, $warnings)
    {

        $this->migrations = $migrations;
        $this->warnings   = $warnings;

        parent::__construct("", 0, null);
    }
}

class NeedToCreateTableException extends Exception
{

}


