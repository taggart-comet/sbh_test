<?php

namespace PhpMyOrm;

use PhpMyOrm\sql\Utils;

/**
 * All the mysql table field classes are here
 * Everyone of them prepares given by user values
 * as for use in a query
 * also to use after fetching the data from db
 */
abstract class Field
{

    public string $description;
    public int    $max_length;
    public bool   $is_primary_key = false;
    public bool   $auto_increment = false;
    public bool   $unsigned       = true;

    public function __construct(
          $is_primary_key = false,
          $description = '',
          $max_length = 0,
          $auto_increment = false
    ) {

        $this->description    = $description;
        $this->is_primary_key = $is_primary_key;
        $this->max_length     = $max_length;
        $this->auto_increment = $auto_increment;
    }

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

final class CharField extends Field
{

    public string $default  = '';
    public string $encoding = 'utf8';
    public string $collate  = 'utf8_general_ci';

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
        $this->default  = $default;

        parent::__construct($is_primary_key, $description, $max_length);
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

    public int $default = 0;

    public function __construct(
          $is_primary_key = false,
          $auto_increment = false,
          $default = 0,
          $description = '',
          $unsigned = true
    ) {

        $this->unsigned = $unsigned;
        $this->default  = $default;

        parent::__construct($is_primary_key, $description, 11, $auto_increment);
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

final class TinyIntField extends IntField
{

    public int $max_length = 1;

    public function __construct($is_primary_key = false, $default = 0, $description = '', $max_length = 1)
    {

        $this->max_length = $max_length;

        parent::__construct($is_primary_key, false, $default, $description, false);
    }
}

class BigIntField extends IntField
{

    public int $max_length = 20;

    public function __construct(
          $max_length = 20,
          $is_primary_key = false,
          $auto_increment = false,
          $default = 0,
          $description = '',
          $unsigned = true
    ) {

        $this->max_length = $max_length;

        parent::__construct(
              $is_primary_key,
              $auto_increment,
              $default,
              $description,
              $unsigned,
        );
    }
}

class TextFieldField extends Field
{

    public string $default  = '';
    public string $encoding = 'utf8';
    public string $collate  = 'utf8_general_ci';

    public function __construct($description = '', $encoding = 'utf8', $collate = 'utf8_general_ci')
    {

        $this->encoding = $encoding;
        $this->collate  = $collate;

        parent::__construct(false, $description);
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

    public string $default  = '';

    public function __construct($description = '')
    {

        parent::__construct(false, $description);
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

