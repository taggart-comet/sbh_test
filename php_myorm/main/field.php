<?php

namespace PhpMyOrm;

use PhpMyOrm\sql\Utils;

/**
 * All the mysql table field classes are here
 * Everyone of them prepares given by user values
 * as for use in a query
 * also to use after fetching the data from db
 */
class Field
{

    public function __construct(
          $is_primary_key = false,
          $default = null,
          $description = '',
          $max_length = 1,
          $auto_increment = false
    ) {

        $this->default        = $default;
        $this->description    = $description;
        $this->is_primary_key = $is_primary_key;
        $this->max_length     = $max_length;
        $this->auto_increment = $auto_increment;
    }

    public $default        = null;
    public $description;
    public $max_length;
    public $is_primary_key = false;
    public $auto_increment = false;
    public $unsigned       = true;

    // should be overridden by children
    public function isCorrectType($value)
    {

        return true;
    }

    // should be overridden by children
    // preparation for sql
    public function prepareValue($value)
    {

        return $value;
    }

    // should be overridden by children
    public function prepareValueForUse($value)
    {

        return $value;
    }

    // should be overridden by children
    public function getSqlType():string
    {

        return '';
    }

    //
    public function getPhpDocType():string
    {

        return 'int';
    }

    // should be overridden by children
    public function getCreateStatement():string
    {

        return '';
    }

}

class CharField extends Field
{

    public $encoding = 'utf8';
    public $collate  = 'utf8_general_ci';

    public function __construct(
          $max_length,
          $is_primary_key = false,
          $default = '',
          $encoding = 'utf8',
          $collate = 'utf8_general_ci',
          $description = ''
    ) {

        $this->encoding = $encoding;
        $this->collate  = $collate;

        parent::__construct($is_primary_key, $default, $description, $max_length);
    }

    public function isCorrectType($value)
    {

        if (gettype($value) != 'string') {
            return false;
        }

        # max length
        if (strlen($value) > $this->max_length) {
            return false;
        }
        return true;
    }

    public function prepareValue($value)
    {

        if ($value === null) {
            return "NULL";
        }

        return Utils::formatString($value);
    }

    public function prepareValueForUse($value)
    {

        return (string)$value;
    }

    public function getSqlType():string
    {

        return "varchar({$this->max_length})";
    }

    public function getCreateStatement():string
    {

        $query = "VARCHAR({$this->max_length}) CHARACTER SET {$this->encoding} COLLATE {$this->collate} NULL DEFAULT '{$this->default}' COMMENT '{$this->description}'";

        if ($this->is_primary_key) {
            $query = "VARCHAR({$this->max_length}) CHARACTER SET {$this->encoding} COLLATE {$this->collate} NOT NULL DEFAULT '{$this->default}' COMMENT '{$this->description}'";
        }

        return $query;
    }

    public function getPhpDocType():string
    {

        return 'string';
    }
}

class IntField extends Field
{

    public $default = 0;

    public function __construct(
          $is_primary_key = false,
          $auto_increment = false,
          $default = 0,
          $description = '',
          $unsigned = true
    ) {

        $this->unsigned = $unsigned;

        parent::__construct($is_primary_key, $default, $description, 11, $auto_increment);
    }

    public function isCorrectType($value)
    {

        if (gettype($value) != 'integer') {
            return false;
        }

        # max length
        if (strlen($value) > $this->max_length) {
            return false;
        }
        return true;
    }

    public function prepareValue($value)
    {

        // for `key + 1` to go through
        if (preg_match("/^.*\s([\+,\-])\s(\d*)$/", $value)) {
            return $value;
        }

        return intval($value);
    }

    public function prepareValueForUse($value)
    {

        return (int)$value;
    }

    public function getSqlType():string
    {

        $type = "int({$this->max_length})";

        if ($this->unsigned) {
            return $type . ' unsigned';
        }

        return $type;
    }

    public function getCreateStatement():string
    {

        $query = "INT({$this->max_length}) " . (($this->unsigned) ? "UNSIGNED" : "");

        $query .= " NOT NULL";

        if ($this->auto_increment) {
            $query .= " AUTO_INCREMENT";
        }

        $query .= " COMMENT '{$this->description}'";

        return $query;
    }

    public function getPhpDocType():string
    {

        return 'int';
    }
}

class TinyIntField extends Field
{

    public $default = 0;

    public function __construct($is_primary_key = false, $default = 0, $description = '', $max_length = 1)
    {

        parent::__construct($is_primary_key, $default, $description, $max_length, false);
    }

    public function isCorrectType($value)
    {

        if (gettype($value) != 'integer') {
            return false;
        }

        # max length
        if (strlen($value) > $this->max_length) {
            return false;
        }
        return true;
    }

    public function prepareValue($value)
    {

        // for `key + 1` to go through
        if (preg_match("/^.*\s([\+,\-])\s(\d*)$/", $value)) {
            return $value;
        }

        return intval($value);
    }

    public function prepareValueForUse($value)
    {

        return (int)$value;
    }

    public function getSqlType():string
    {

        return "tinyint({$this->max_length})";
    }

    public function getCreateStatement():string
    {

        return "TINYINT({$this->max_length})  NULL  DEFAULT '{$this->default}' COMMENT '{$this->description}'";
    }

    public function getPhpDocType():string
    {

        return 'int';
    }
}

class BigIntField extends Field
{

    public $default = 0;

    public function __construct(
          $max_length = 20,
          $is_primary_key = false,
          $auto_increment = false,
          $default = 0,
          $description = '',
          $unsigned = true
    ) {

        $this->unsigned = $unsigned;

        parent::__construct($is_primary_key, $default, $description, $max_length, $auto_increment);
    }

    public function isCorrectType($value)
    {

        if (!is_numeric($value)) {
            return false;
        }

        # max length
        if (strlen($value) > $this->max_length) {
            return false;
        }

        return true;
    }

    public function prepareValue($value)
    {

        // for `key + 1` to go through
        if (preg_match("/^.*\s([\+,\-])\s(\d*)$/", $value)) {
            return $value;
        }

        return (string) $value;
    }

    public function prepareValueForUse($value)
    {

        return (int)$value;
    }

    public function getSqlType():string
    {

        $type = "bigint({$this->max_length})";

        if ($this->unsigned) {
            return $type . ' unsigned';
        }

        return $type;
    }

    public function getCreateStatement():string
    {

        $query = "BIGINT({$this->max_length}) " . (($this->unsigned) ? "UNSIGNED " : "");

        $query .= " NOT NULL";

        if ($this->auto_increment) {
            $query .= " AUTO_INCREMENT";
        }

        $query .= " COMMENT '{$this->description}'";

        return $query;
    }

    public function getPhpDocType():string
    {

        return 'int';
    }
}

class TextFieldField extends Field
{

    public $encoding = 'utf8';
    public $collate  = 'utf8_general_ci';

    public function __construct($description = '', $encoding = 'utf8', $collate = 'utf8_general_ci')
    {

        $this->encoding = $encoding;
        $this->collate  = $collate;

        parent::__construct(false, '', $description, null, false);
    }

    public function isCorrectType($value)
    {

        if (gettype($value) != 'string') {
            return false;
        }

        return true;
    }

    public function prepareValue($value)
    {

        if ($value === null) {
            return "NULL";
        }

        return Utils::formatString($value);
    }

    public function prepareValueForUse($value)
    {

        return (string)$value;
    }

    public function getSqlType():string
    {

        return "text";
    }

    public function getCreateStatement():string
    {

        return "TEXT CHARACTER SET {$this->encoding} COLLATE {$this->collate} NULL COMMENT '{$this->description}'";
    }

    public function getPhpDocType():string
    {

        return 'string';
    }
}

class JSONField extends Field
{

    public function __construct($description = '')
    {

        parent::__construct(false, [], $description, null, false);
    }

    public function isCorrectType($value)
    {

        if (gettype($value) == 'array') {
            return true;
        }

        return false;
    }

    public function prepareValue($value)
    {

        return json_encode($value);
    }

    public function prepareValueForUse($value)
    {

        $output = json_decode($value, true, 512, JSON_BIGINT_AS_STRING);
        if (!is_array($output)) {

            // this hell is because of escaping braces, better to find out ho to make not so ugly
            $output = json_decode($output, true, 512, JSON_BIGINT_AS_STRING);
            if (!is_array($output)) {
                return [];
            }
        }
        return $output;
    }

    public function getSqlType():string
    {

        return "json";
    }

    public function getCreateStatement():string
    {

        return "JSON NULL COMMENT '{$this->description}'";
    }

    public function getPhpDocType():string
    {

        return 'array';
    }
}

class BinaryField extends Field
{

    public function __construct($max_length, $default = '', $description = '')
    {

        parent::__construct(false, $default, $description, $max_length, false);
    }

    public function isCorrectType($value)
    {

        if (gettype($value) != 'resource') {
            return false;
        }

        # max length
        if (count($value) > $this->max_length) {
            return false;
        }
        return true;
    }

    public function getSqlType():string
    {

        return "binary({$this->max_length})";
    }

    public function getCreateStatement():string
    {

        return "BINARY({$this->max_length}) NULL DEFAULT '{$this->default}' COMMENT '{$this->description}'";
    }

    public function getPhpDocType():string
    {

        return 'resource';
    }
}
