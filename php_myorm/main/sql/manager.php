<?php

namespace PhpMyOrm\sql;

use PhpMyOrm\Model;

/**
 * Handles selections, filters, etc
 * Called on Model::objects-><>
 */
class SqlManager
{

    /** @var Model */
    protected $_empty_model_instance;

    // where filter
    protected $_where_set = [];

    // order by
    protected $_order_by_set = [];

    public function __construct($model_instance)
    {

        $this->_empty_model_instance = $model_instance;
    }

    // -------------------------------------------------------
    // Query making methods
    // -------------------------------------------------------

    /** @return Model | Model[] */
    public function get($limit = 1, $offset = 0)
    {

        $query_conf = QueryBuilder::select(
              $this->_empty_model_instance->table_name,
              $this->_where_set,
              $this->_order_by_set,
              $this->_empty_model_instance->getFieldTypes(),
              $offset, $limit
        );

        if ($limit == 1) {
            $result = MysqlAdapter::init($this->_empty_model_instance->db_name)->getOne($query_conf['query'],
                  $query_conf['set']);
        } else {
            $result = MysqlAdapter::init($this->_empty_model_instance->db_name)->getMany($query_conf['query'],
                  $query_conf['set']);
        }

        $model_class = get_class($this->_empty_model_instance);

        if (empty($result)) {
            throw new DoesNotExist("Row not found for model: {$model_class}");
        }

        if ($limit == 1 && count($result)) {
            return new $model_class($result, true);
        }

        $obj_array = [];
        foreach ($result as $row) {
            $obj_array[] = new $model_class($row, true);
        }

        return $obj_array;
    }

    //
    public function getList($limit = 1, $offset = 0, $as_array_list = false)
    {

        try {
            $result = $this->get($limit, $offset);
        } catch (DoesNotExist $e) {
            return [];
        }

        if (is_array($result)) {
            if ($as_array_list) {

                $output = [];
                foreach ($result as $row) {
                    $output[] = $row->getAsArray();
                }

                return $output;
            }
            return $result;
        }

        if ($as_array_list) {
            return [
                  $result->getAsArray(),
            ];
        }

        return [$result];
    }

    //
    public function count():int
    {

        $query_conf = QueryBuilder::count(
              $this->_empty_model_instance->table_name,
              $this->_where_set
        );

        //		print_r($query_conf);exit;
        $result = MysqlAdapter::init($this->_empty_model_instance->db_name)->getOne($query_conf['query'],
              $query_conf['set']);

        if (!isset($result[QueryBuilder::COUNT_NAME])) {
            return 0;
        }

        return $result[QueryBuilder::COUNT_NAME];
    }

    //
    public function delete($limit = 1)
    {

        $query_conf = QueryBuilder::delete(
              $this->_empty_model_instance->table_name,
              $this->_where_set,
              $limit
        );

        MysqlAdapter::init($this->_empty_model_instance->db_name)
              ->executeQuery($query_conf['query'], $query_conf['set']);
    }

    //
    public function updateMany($update_set, $limit)
    {

        $query_conf = QueryBuilder::update(
              $this->_empty_model_instance->table_name,
              $this->_where_set,
              $this->_prepareSet($update_set),
              $limit
        );

        MysqlAdapter::init($this->_empty_model_instance->db_name)
              ->executeQuery($query_conf['query'], $query_conf['set']);
    }

    // -------------------------------------------------------
    // Filters
    // -------------------------------------------------------

    // be careful here with order of PKs
    public function filter_primary():self
    {

        $args = func_get_args();

        if (count($args) != count($this->_empty_model_instance->primary_keys)) {

            if (is_array($args[0])) {
                $args = $args[0];
                if (count($args) != count($this->_empty_model_instance->primary_keys)) {
                    throw new IncorrectFilterParams("Args for `filter_primary` do not match the number of primary keys");
                }
            } else {
                throw new IncorrectFilterParams("Args for `filter_primary` do not match the number of primary keys");
            }
        }

        $set = [];
        foreach ($this->_empty_model_instance->primary_keys as $i => $pk) {
            $set[$pk] = $this->_empty_model_instance->getFieldTypes()[$pk]->prepareValue($args[$i]);
        }

        if (!isset($this->_where_set[QueryBuilder::FILTER_EQUAL])) {
            $this->_where_set[QueryBuilder::FILTER_EQUAL] = [];
        }

        $this->_where_set[QueryBuilder::FILTER_EQUAL] = array_merge($this->_where_set[QueryBuilder::FILTER_EQUAL],
              $set);

        return $this;
    }

    public function filter_equal(string $field_name, $value):self
    {

        // field check
        $this->_isFieldLegit($field_name);

        $this->_where_set[QueryBuilder::FILTER_EQUAL][$field_name] = $this->_empty_model_instance->getFieldTypes()[$field_name]->prepareValue($value);

        return $this;
    }

    public function filter_not_equal(string $field_name, $value):self
    {

        // field check
        $this->_isFieldLegit($field_name);

        $this->_where_set[QueryBuilder::FILTER_NOT_EQUAL][$field_name] = $this->_empty_model_instance->getFieldTypes()[$field_name]->prepareValue($value);

        return $this;
    }

    public function filter_more(string $field_name, $more_than):self
    {

        // field check
        $this->_isFieldLegit($field_name);

        $this->_where_set[QueryBuilder::FILTER_MORE][$field_name] = $this->_empty_model_instance->getFieldTypes()[$field_name]->prepareValue($more_than);

        return $this;
    }

    public function filter_less(string $field_name, $less_than):self
    {

        // field check
        $this->_isFieldLegit($field_name);

        $this->_where_set[QueryBuilder::FILTER_LESS][$field_name] = $this->_empty_model_instance->getFieldTypes()[$field_name]->prepareValue($less_than);

        return $this;
    }

    public function filter_between(string $field_name, int $from, int $to):self
    {

        if (isset($this->_where_set[QueryBuilder::FILTER_BETWEEN])) {
            throw new IncorrectFilterParams("There can't be more than one `filter_between` in a single query");
        }

        if ($from > $to) {
            throw new IncorrectFilterParams("For `filter_between` param `from` should be less than `to`");
        }

        // field check
        $this->_isFieldLegit($field_name);

        $this->_where_set[QueryBuilder::FILTER_BETWEEN] = [
              'field_name' => $field_name,
              'params'     => [
                    $this->_empty_model_instance->getFieldTypes()[$field_name]->prepareValue($from),
                    $this->_empty_model_instance->getFieldTypes()[$field_name]->prepareValue($to),
              ],
        ];

        return $this;
    }

    public function filter_in(string $field_name, array $value_list):self
    {

        if (isset($this->_where_set[QueryBuilder::FILTER_IN])) {
            throw new IncorrectFilterParams("There can't be more than one `filter_in` in a single query");
        }

        // field check
        $this->_isFieldLegit($field_name);

        $prepared_value_list = [];
        foreach ($value_list as $value) {
            $prepared_value_list[] = $this->_empty_model_instance->getFieldTypes()[$field_name]->prepareValue($value);
        }

        $this->_where_set[QueryBuilder::FILTER_IN] = [
              'field_name' => $field_name,
              'array_in'   => $prepared_value_list,
        ];

        return $this;
    }

    // -------------------------------------------------------
    // ORDER BY
    // -------------------------------------------------------

    public function order_asc(string $field_name):self
    {

        // field check
        $this->_isFieldLegit($field_name);

        $this->_order_by_set[QueryBuilder::ORDER_BY_ASC][] = $field_name;

        return $this;
    }

    public function order_desc(string $field_name):self
    {

        // field check
        $this->_isFieldLegit($field_name);

        $this->_order_by_set[QueryBuilder::ORDER_BY_DESC][] = $field_name;

        return $this;
    }

    // -------------------------------------------------------
    // Static (here values come prepared)
    // -------------------------------------------------------

    //
    public static function insert($db_name, $table, $set)
    {

        # make query
        $query = QueryBuilder::insert(
              $table,
              $set
        );

        return MysqlAdapter::init($db_name)->insert($query, $set);
    }

    //
    public static function updateOne($db_name, $table, $set, $where_set)
    {

        # make query
        $update_conf = QueryBuilder::update($table, [
              QueryBuilder::FILTER_EQUAL => $where_set,
        ], $set, 1);

        return MysqlAdapter::init($db_name)->executeQuery($update_conf['query'], $update_conf['set']);
    }

    // on duplicated key update
    public static function insertOrUpdate($db_name, $table, $insert_set, $update_set)
    {

        # make query
        $insert_conf = QueryBuilder::insertOrUpdate(
              $table,
              $insert_set,
              $update_set
        );

        return MysqlAdapter::init($db_name)->insert($insert_conf['query'], $insert_conf['set']);
    }

    // -------------------------------------------------------
    // Utils
    // -------------------------------------------------------

    // executes a custom query on this database
    public function runCustomQuery($query, $set = [])
    {

        $result = MysqlAdapter::init($this->_empty_model_instance->db_name)
              ->getMany($query, $set);

        return $result;
    }

    public function getColumns():array
    {

        $result = MysqlAdapter::init($this->_empty_model_instance->db_name)
              ->getMany("SHOW COLUMNS FROM `{$this->_empty_model_instance->table_name}`;", []);

        return $result;
    }

    public function getIndexes():array
    {

        $result = MysqlAdapter::init($this->_empty_model_instance->db_name)
              ->getMany("SHOW INDEX FROM `{$this->_empty_model_instance->table_name}`;", []);

        return $result;
    }

    public function getTableStatus():array
    {

        $result = MysqlAdapter::init($this->_empty_model_instance->db_name)
              ->getMany("SHOW TABLE STATUS LIKE `{$this->_empty_model_instance->table_name}`;", []);

        return $result;
    }

    //
    protected function _isFieldLegit(string $field_name)
    {

        $fields = $this->_empty_model_instance->getFieldTypes();

        // checking field name
        if (!isset($fields[$field_name])) {
            $class_name = get_class($this->_empty_model_instance);
            throw new ModelPropertyDoesExist("Field `{$field_name}` is not present in the model `{$class_name}`");
        }
    }

    //
    private function _prepareSet(array $set):array
    {

        $fields = $this->_empty_model_instance->getFieldTypes();

        $output = [];
        foreach ($set as $field_name => $value) {

            // checking field name
            if (!isset($fields[$field_name])) {
                $class_name = get_class($this->_empty_model_instance);
                throw new ModelPropertyDoesExist("Field `{$field_name}` is not present in the model `{$class_name}`");
            }

            // preparing values
            $output[$field_name] = $fields[$field_name]->prepareValue($value);
        }

        return $output;
    }

}

