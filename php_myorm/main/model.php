<?php

namespace PhpMyOrm;

use PhpMyOrm\sql\Mysql;
use PhpMyOrm\sql\MysqlAdapter;
use PhpMyOrm\sql\SqlManager;
use PhpMyOrm\sql\Utils;

/**
 * Main interface class, that gets implemented by model classes
 */
abstract class Model
{

    // -------------------------------------------------------
    // CUSTOMIZABLE
    // -------------------------------------------------------

    public string $table_name;
    public string $db_name    = 'default';
    public string $description = '';
    public string $charset     = 'utf8';
    public string $engine      = 'InnoDB';

    /**
     * @return Field[]
     */
    public function fields()
    {

        /**
         * Defines table fields, example:
         * [
         * 'user_id'    => new db\IntField(true),
         * 'date_added' => new db\IntField(),
         * 'full_name'  => new db\CharField(200),
         * 'extra'      => new db\JSONField(),
         * ];
         */
        return [];
    }

    # defines a list of indexes
    public function indexes()
    {

        # example [['field1', 'field2'], ['field1', 'field2']]
        return [];
    }

    // -------------------------------------------------------
    // INIT
    // -------------------------------------------------------

    /**
     * Model constructor.
     *
     * @param array $set_fields
     * @param bool  $is_fetched
     *
     * @throws IncorrectModelSetup
     */
    public function __construct(array $set_fields = [], $is_fetched = false)
    {

        # checking if table name is set
        if (is_null($this->table_name)) {
            throw new IncorrectModelSetup("table_name is not defined");
        }

        # checking fields() are correct
        $this->_checkFields();

        # checking indexes
        $this->_checkIndexes();

        # checking what fields we can set from $fields parameter
        $this->_assignFields($set_fields);

        # to know is it was created manually as new or was it fetched from db
        $this->_is_fetched = $is_fetched;
    }

    public function __get($name)
    {

        # for table fields
        if (isset($this->_field_types[$name])) {
            return $this->_field_types[$name]->prepareValueForUse($this->_field_values[$name]);
        }

        throw new ModelPropertyDoesExist("Model property `{$name}` does not exist");
    }

    public function __set($name, $value)
    {

        if (isset($this->_field_values[$name])) {

            if (!$this->_field_types[$name]->isCorrectType($value)) {
                throw new IncorrectModelInstanceCall(
                      "Incorrect type given for field `{$name}` in the constructor."
                );
            }

            $this->_field_values[$name] = $this->_field_types[$name]->prepareValue($value);
            return;
        }

        throw new ModelPropertyDoesExist("Model property `{$name}` does not exist");
    }

    // -------------------------------------------------------
    // FINAL
    // -------------------------------------------------------

    /**
     * What declared by user in `fields()` methods
     * @var Field[]
     */
    private $_field_types = [];

    # if the row exists in the db or it needs to be inserted on save()
    private $_is_fetched = false;

    //
    private $_field_values = [];

    //
    public $primary_keys       = [];
    public $primary_key_values = [];

    //
    final public function getAsArray()
    {

        $output = [];
        foreach ($this->_field_values as $field_name => $value) {
            $output[$field_name] = $this->_field_types[$field_name]->prepareValueForUse($value);
        }

        return $output;
    }

    final public function save()
    {

        # updating the object's row
        if ($this->_is_fetched) {

            $set = [];
            foreach ($this->_field_values as $key => $value) {
                if (in_array($key, $this->primary_keys)) {
                    continue;
                }
                $set[$key] = $value;
            }

            return SqlManager::updateOne(
                  $this->db_name,
                  $this->table_name,
                  $set,
                  $this->primary_key_values
            );
        }

        // marking that the row now exists in the db
        $this->_is_fetched = true;

        # the object is newly created -> inserting
        return SqlManager::insert(
              $this->db_name,
              $this->table_name,
              $this->_getSetForInsert()
        );
    }

    final public function saveOrUpdate($on_update_set)
    {

        // checking if fields are typed correctly
        $update_set_verified = [];
        foreach ($on_update_set as $field_name => $v) {
            if (!isset($this->_field_types[$field_name])) {
                throw new IncorrectUpdateParams("Model does not have the field `{$field_name}`");
            }

            $update_set_verified[$field_name] = $this->_field_types[$field_name]->prepareValue($v);
        }

        return SqlManager::insertOrUpdate(
              $this->db_name,
              $this->table_name,
              $this->_getSetForInsert(),
              $update_set_verified
        );
    }

    // -------------------------------------------------------
    // SQL MANAGER ACCESS
    // -------------------------------------------------------

    final public static function objects($custom_table = null)
    {

        if (is_null($custom_table)) {
            $class = get_called_class();
            return new SqlManager(new $class());
        }

        $class                = get_called_class();
        $instance             = new $class();
        $instance->table_name = $custom_table;

        return new SqlManager($instance);
    }

    // WARNING this query goes PDO::unprepared
    // use with caution
    final public static function insertMany(array $insert_ar)
    {

        $class = get_called_class();

        /** @var Model $instance */
        $instance        = new $class();
        $instance_fields = $instance->getFieldTypes();

        // -------------------------------------------------------
        // Preparing the insert values
        // -------------------------------------------------------
        $prepared_insert_ar = [];
        foreach ($insert_ar as $set) {
            foreach ($set as $field_name => $value) {

                // checking if field exists
                if (!isset($instance_fields[$field_name])) {
                    throw new ModelPropertyDoesExist("Field `{$field_name}` is not present in the model");
                }
            }
            $prepared_insert_ar[] = $set;
        }

        // -------------------------------------------------------
        // Building query
        // -------------------------------------------------------

        $fields = Utils::makeFieldNameList($prepared_insert_ar[0]);
        $query  = "INSERT INTO `{$instance->table_name}` ({$fields}) VALUES ";
        foreach ($prepared_insert_ar as $set) {
            $values = Utils::makeValueListUnPrepared($set);
            $query  .= "({$values}), ";
        }

        $query = rtrim($query, ', ');

        MysqlAdapter::init($instance->db_name)->insertMany($query);
    }

    // -------------------------------------------------------
    // TRANSACTIONS
    // -------------------------------------------------------

    final public static function startTransaction()
    {

        return self::_getMysqlAdapter()->beginTransaction();
    }

    final public static function commitTransaction()
    {

        return self::_getMysqlAdapter()->commit();
    }

    final public static function rollBackTransaction()
    {

        return self::_getMysqlAdapter()->rollBack();
    }

    // -------------------------------------------------------
    // UTILS
    // -------------------------------------------------------

    // returns pk value, array if many
    final public function pk()
    {

        if (count($this->primary_keys) == 1) {
            return $this->primary_key_values[$this->primary_keys[0]];
        }

        $output = [];
        foreach ($this->primary_keys as $field_name) {
            $output[] = $this->primary_key_values[$field_name];
        }

        return $output;
    }

    //
    final public function getFieldTypes()
    {

        return $this->_field_types;
    }

    // checks if fields() are all Field() and there's a PK
    private function _checkFields()
    {

        $child_fields = $this->fields();

        $has_primary_key = false;
        $temp_ar         = [];
        foreach ($child_fields as $field_name => $child_field) {
            if (!$child_field instanceof Field) {
                throw new IncorrectModelSetup("Field {$field_name} is filled incorrectly!");
            }

            if ($child_field->is_primary_key) {
                $has_primary_key      = true;
                $this->primary_keys[] = $field_name;
            }

            $temp_ar[$field_name] = $child_field;
        }

        # adding auto increment primary field if non is given
        if (!$has_primary_key) {
            $this->_field_types['id'] = new IntField(true, true);
            $this->primary_keys[]     = 'id';
        }

        foreach ($temp_ar as $k => $v) {
            $this->_field_types[$k] = $v;
        }
    }

    // check if indexes() are given for existent fields
    private function _checkIndexes()
    {

        foreach ($this->indexes() as $index) {
            foreach ($index as $key => $field) {
                if ($key == 0) {
                    $index_name = $field;
                    continue;
                }

                if (!isset($this->_field_types[$field])) {
                    throw new IncorrectModelSetup(
                          "Incorrect index `{$index_name}` for nonexistent field: `{$field}``"
                    );
                }
            }
        }
    }

    //
    private function _assignFields($set_fields)
    {

        foreach ($this->_field_types as $field_key => $field_class) {

            if (isset($set_fields[$field_key])) {

                # adding the given value as fields value
                $this->_field_values[$field_key] = $field_class->prepareValue($set_fields[$field_key]);

                # assigning primary keys
                if ($field_class->is_primary_key) {
                    $this->primary_key_values[$field_key] = $field_class->prepareValue($set_fields[$field_key]);
                }
                continue;
            }

            # adding the default value
            $this->_field_values[$field_key] = $field_class->prepareValue($field_class->default);

            # assigning primary keys
            if ($field_class->is_primary_key) {
                $this->primary_key_values[$field_key] = $field_class->prepareValue($field_class->default);
            }
        }
    }

    //
    private static function _getMysqlAdapter():Mysql
    {

        $class = get_called_class();

        /** @var Model $instance */
        $instance = new $class();

        return MysqlAdapter::init($instance->db_name);
    }

    // preparing insert set
    // if autoincrement, removing PK from value list
    private function _getSetForInsert():array
    {

        $output = [];
        foreach ($this->_field_types as $field_name => $field) {
            if ($field->auto_increment) {
                continue;
            }
            $output[$field_name] = $this->_field_values[$field_name];
        }

        return $output;
    }
}
