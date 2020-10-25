<?php

namespace PhpMyOrm\sql;

class Utils
{

    public static function makeFieldNameList(array $set):string
    {

        if (count($set) < 1) {
            return '';
        }

        $output = '';
        foreach ($set as $key => $value) {
            $output .= self::wrap($key, '`') . ', ';
        }

        return self::removeLast($output, 2);
    }

    public static function makeUpdateConf(array $set)
    {

        if (count($set) < 1) {
            return '';
        }

        $query      = '';
        $output_set = [];
        foreach ($set as $key => $value) {

            // this is for `field` + 1 or `field` - 2 queries
            if (preg_match("/^{$key}\s([\+,\-])\s(\d*)$/", $value, $matches)) {

                $query .= self::wrap($key, '`') . '=' . self::wrap($key, '`') . "{$matches[1]}{$matches[2]}, ";
                continue;
            }

            // normal field
            $query                                      .= self::wrap($key, '`') . '=' . self::_param($key,
                        '_update_') . ', ';
            $output_set[self::_param($key, '_update_')] = $value;
        }

        $query = self::removeLast($query, 2);

        return [
              'query' => $query,
              'set'   => $output_set,
        ];
    }

    public static function makeValueList(array $set):string
    {

        if (count($set) < 1) {
            return '';
        }

        $output = '';
        foreach ($set as $key => $value) {
            $output .= ':' . $key . ', ';
        }

        return self::removeLast($output, 2);
    }

    public static function makeValueListUnPrepared(array $set):string
    {

        if (count($set) < 1) {
            return '';
        }

        $output = '';
        foreach ($set as $key => $value) {
            $output .= '\'' . $value . '\', ';
        }

        return self::removeLast($output, 2);
    }

    public static function makeWhereConf(array $where_set):array
    {

        $string = '';
        $set    = [];

        foreach ($where_set as $condition_type => $condition) {
            switch ($condition_type) {
                case QueryBuilder::FILTER_EQUAL:
                    $string .= self::_makeSimpleWhereString($condition, '=');
                    $set    = array_merge($set, self::_paramArray($condition));
                    break;
                case QueryBuilder::FILTER_NOT_EQUAL:
                    $string .= self::_makeSimpleWhereString($condition, '!=');
                    $set    = array_merge($set, self::_paramArray($condition));
                    break;
                case QueryBuilder::FILTER_MORE:
                    $string .= self::_makeSimpleWhereString($condition, '>');
                    $set    = array_merge($set, self::_paramArray($condition));
                    break;
                case QueryBuilder::FILTER_LESS:
                    $string .= self::_makeSimpleWhereString($condition, '<');
                    $set    = array_merge($set, self::_paramArray($condition));
                    break;
                case QueryBuilder::FILTER_BETWEEN:
                    $conf = self::_makeWhereBetweenConf(
                          $condition['field_name'],
                          $condition['params'][0],
                          $condition['params'][1]
                    );

                    $string .= ' ' . $conf['string'];
                    $set    = array_merge($set, $conf['set']);
                    break;
                case QueryBuilder::FILTER_IN:

                    $conf = self::_makeWhereInConf(
                          $condition['field_name'],
                          $condition['array_in']
                    );

                    $string .= ' ' . $conf['string'];

                    $set = array_merge($set, $conf['set']);
                    break;
                default:
                    break;
            }
        }

        return [
              'string' => rtrim($string, ' AND '),
              'set'    => $set,
        ];
    }


    // -------------------------------------------------------
    // UTILS
    // -------------------------------------------------------

    public static function wrap($string, $quote)
    {

        return $quote . $string . $quote;
    }

    public static function removeLast($string, $chars_to_remove = 0)
    {

        if ($chars_to_remove == 0) {
            return $string;
        }

        return substr($string, 0, -$chars_to_remove);
    }

    // https://stackoverflow.com/questions/1401317/remove-non-utf8-characters-from-string
    public static function formatString($value)
    {

        $regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;

        return trim(preg_replace($regex, '$1', $value));
    }

    public static function consoleLog($message)
    {

        $conf = require __DIR__ . '/../../conf.php';

        if (!isset($conf['SHOW_DEBUG']) || !$conf['SHOW_DEBUG']) {
            return;
        }

        if (php_sapi_name() != "cli") {
            return;
        }

        $v = print_r($message, true);
        echo $v . PHP_EOL;
    }

    // -------------------------------------------------------
    // WHERE makers
    // -------------------------------------------------------

    protected static function _makeSimpleWhereString(array $where_set, string $operator):string
    {

        if (count($where_set) < 1) {
            return '';
        }

        $output = '';
        foreach ($where_set as $key => $value) {
            $output .= "`{$key}`" . $operator . self::_param($key) . ' AND ';
        }

        return $output;
    }

    protected static function _makeWhereBetweenConf(string $field_name, int $from, int $to):array
    {

        $string = "`{$field_name}` BETWEEN :_from AND :_to";
        $set    = [
              '_from' => $from,
              '_to'   => $to,
        ];

        return [
              'string' => $string,
              'set'    => $set,
        ];
    }

    protected static function _makeWhereInConf(string $field_name, array $array_in):array
    {

        $string = "`{$field_name}` IN (";

        $i   = 0;
        $set = [];
        foreach ($array_in as $value) {
            $el_name = 'in_el_' . $i;
            $string  .= ':' . $el_name . ', ';

            $set[$el_name] = $value;

            //
            $i++;
        }

        $string = self::removeLast($string, 2) . ')';

        return [
              'string' => $string,
              'set'    => $set,
        ];
    }

    protected static function _param(string $parameter_name, $prefix = '_where_'):string
    {

        return ':' . $prefix . $parameter_name;
    }

    protected static function _paramArray(array $set):array
    {

        $new_set = [];
        foreach ($set as $k => $v) {
            $new_set[self::_param($k)] = $v;
        }

        return $new_set;
    }
}
