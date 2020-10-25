<?php

namespace PhpMyOrm\sql;

/**
 * Query building class here
 */
class QueryBuilder
{

    //
    const FILTER_EQUAL     = 'equal';
    const FILTER_NOT_EQUAL = 'not_equal';
    const FILTER_MORE      = 'more';
    const FILTER_LESS      = 'less';
    const FILTER_BETWEEN   = 'between';
    const FILTER_IN        = 'in';

    //
    const ORDER_BY_ASC  = 'order_by_asc';
    const ORDER_BY_DESC = 'order_by_desc';

    //
    const COUNT_NAME = '_cc';

    // -------------------------------------------------------
    // INSERT
    // -------------------------------------------------------

    public static function insert($table_name, $set):string
    {

        $fields = Utils::makeFieldNameList($set);
        $values = Utils::makeValueList($set);

        return "INSERT INTO `{$table_name}` ({$fields}) VALUES ($values);";
    }

    public static function insertOrUpdate($table_name, $insert_set, $update_set):array
    {

        $fields        = Utils::makeFieldNameList($insert_set);
        $insert_values = Utils::makeValueList($insert_set);
        $update_conf   = Utils::makeUpdateConf($update_set);

        return [
              'query' => "INSERT INTO `{$table_name}` ({$fields}) VALUES ({$insert_values}) on duplicate key update {$update_conf['query']};",
              'set'   => array_merge($insert_set, $update_conf['set']),
        ];
    }

    // -------------------------------------------------------
    // SELECT
    // -------------------------------------------------------

    public static function select(
          string $table_name,
          array $where_set,
          array $order_by_set,
          array $set,
          int $offset,
          int $limit
    ):array {

        $fields = Utils::makeFieldNameList($set);

        $from_where_part   = self::_getFromWherePart($table_name, $where_set);
        $order_by_part     = self::_getOrderByPart($order_by_set);
        $limit_offset_part = self::_getLimitOffsetPart($offset, $limit);

        $query = "SELECT {$fields} {$from_where_part['part']} {$order_by_part} {$limit_offset_part}";

        return [
              'query' => $query,
              'set'   => $from_where_part['set'],
        ];
    }

    public static function count(string $table_name, array $where_set):array
    {

        $from_where_part = self::_getFromWherePart(
              $table_name, $where_set, 0, 1);

        $query = "SELECT COUNT(*) as `" . self::COUNT_NAME . "` " . $from_where_part['part'];

        return [
              'query' => $query,
              'set'   => $from_where_part['set'],
        ];
    }

    // -------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------

    public static function update(string $table_name, array $where_set, array $update_set, int $limit):array
    {

        $update_conf = Utils::makeUpdateConf($update_set);
        $where_conf  = Utils::makeWhereConf($where_set);

        $query = "UPDATE `{$table_name}` SET {$update_conf['query']} WHERE {$where_conf['string']} LIMIT {$limit}";

        return [
              'query' => $query,
              'set'   => array_merge($update_conf['set'], $where_conf['set']),
        ];
    }

    // -------------------------------------------------------
    // DELETE
    // -------------------------------------------------------

    public static function delete(string $table_name, array $where_set, int $limit)
    {

        $from_where_part = self::_getFromWherePart(
              $table_name, $where_set, 0, $limit);

        $query = "DELETE " . $from_where_part['part'];

        return [
              'query' => $query,
              'set'   => $from_where_part['set'],
        ];
    }

    // -------------------------------------------------------
    // PROTECTED
    // -------------------------------------------------------

    public static function _getFromWherePart(string $table_name, array $where_set):array
    {

        $where_conf = Utils::makeWhereConf($where_set);

        $query = "FROM `{$table_name}`";

        if (count($where_set) > 0) {
            $query .= " WHERE {$where_conf['string']}";
        }

        if (count($where_set) > 0) {
            $output = [
                  'part' => $query,
                  'set'  => $where_conf['set'],
            ];
        } else {
            $output = [
                  'part' => $query,
                  'set'  => [],
            ];
        }

        return $output;
    }

    public static function _getOrderByPart(array $order_by_set)
    {

        if (count($order_by_set) < 1) {
            return '';
        }

        $query = 'ORDER BY';

        foreach ($order_by_set as $order_type => $order_fields) {

            foreach ($order_fields as $field_name) {

                $query .= " `{$field_name}` ";

                if ($order_type == self::ORDER_BY_ASC) {
                    $query .= 'ASC';
                }
                if ($order_type == self::ORDER_BY_DESC) {
                    $query .= 'DESC';
                }

                $query .= ',';
            }
        }

        return rtrim($query, ',');
    }

    public static function _getLimitOffsetPart(int $offset, int $limit):string
    {

        if ($offset > 0) {
            $query = " LIMIT {$offset},{$limit}";
        } else {
            $query = " LIMIT {$limit}";
        }

        return $query;
    }
}
