<?php

namespace PhpMyOrm\migrate;

use PDOException;
use PhpMyOrm\Model;

// checks table on structure and indexes
// form a list of migrations then asks user
// if he's okay to implement this list right now,
// knowing that he can potentially loose hist data
// or get hist table locked for some time (depending on it's size)

class MigrateMain
{

    /** @var string */
    protected $_model_class;

    /** @var Model $_model_instance */
    protected $_model_instance = null;

    //
    protected $_migrations = [];

    //
    public $warnings = [];

    public function __construct($model_class)
    {

        $this->_model_class = $model_class;
    }

    // checking that a table exists and it has the correct structure
    public function check()
    {

        // Checking main structure
        $this->_checkTableStructure();

        // Checking indexes
        $this->_checkIndexes();

        // checking if there're any migrations generated
        if (count($this->_migrations) > 0) {
            throw new NeedToMigrateException($this->_migrations, $this->warnings);
        }
        // all good
    }

    //
    public function createTable()
    {

        $table_name = $this->_getModelInstance()->table_name;

        // head
        $query = "CREATE TABLE `{$table_name}` (";

        // columns
        $php_doc = [];
        foreach ($this->_getModelInstance()->getFieldTypes() as $column_name => $field) {
            $query     .= "`{$column_name}` " . $field->getCreateStatement() . ', ';
            $php_doc[] = " * @property \${$column_name} " . $field->getPhpDocType();
        }

        // primary keys
        $query .= "PRIMARY KEY (";
        foreach ($this->_getModelInstance()->primary_keys as $pk) {
            $query .= "`{$pk}`, ";
        }
        $query = rtrim($query, ' ,');
        $query .= "),";

        // indexes
        foreach ($this->_getModelInstance()->indexes() as $index_columns) {
            $index_name = self::_getIndexName($index_columns);
            $query      .= "KEY `{$index_name}` (";
            foreach ($index_columns as $index_column_name) {
                $query .= "`{$index_column_name}`, ";
            }
            $query = rtrim($query, ' ,');
            $query .= "),";
        }

        $query = rtrim($query, ',');

        // footer
        $engine      = $this->_getModelInstance()->engine;
        $charset     = $this->_getModelInstance()->charset;
        $description = $this->_getModelInstance()->description;
        $query       .= ") ENGINE={$engine} DEFAULT CHARSET={$charset} COMMENT='{$description}';";

        //		print_r($query);exit;

        // creating
        $this->_model_class::objects()->runCustomQuery($query);

        // adding user notification with phpdoc help
        $message = "Table `{$table_name}` was created." . PHP_EOL;
        $message .= "Here's phpDoc comments to add to your model: " . PHP_EOL;
        $message .= '/**' . PHP_EOL . ' * ' . $description . PHP_EOL;
        foreach ($php_doc as $php_doc_str) {
            $message .= $php_doc_str . PHP_EOL;
        }
        $message          .= '*/' . PHP_EOL;
        $message          .= "No no stop. You're welcome!";
        $this->warnings[] = $message;
    }

    //
    public function runMigrations()
    {

        foreach ($this->_migrations as $migration) {
            $this->_model_class::objects()->runCustomQuery($this->_prepareMigration($migration));
        }
    }

    // -------------------------------------------------------
    // Protected
    // -------------------------------------------------------

    protected function _checkTableStructure()
    {

        try {
            $fetched_fields = self::_prepareFetchedFields(
                  $this->_model_class::objects()->getColumns()
            );
        } catch (PDOException $e) {

            // table does not exist
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1146) {
                throw new NeedToCreateTableException();
            } else {
                throw $e;
            }
        }

        // checking if there're missing fields and their types
        $model_fields = $this->_getModelInstance()->getFieldTypes();

        foreach ($model_fields as $field_name => $model_field) {

            // if the column exist
            if (!isset($fetched_fields[$field_name])) {

                $this->warnings[] = "New column `{$field_name}` will be added to your table";

                // adding migration to add the field
                $this->_migrations[] = self::_getAddColumnStatement($field_name, $model_field->getCreateStatement());
                continue;
            }

            // if the type is right
            if ($model_field->getSqlType() != $fetched_fields[$field_name]['Type']) {

                $message          = "Type of column `{$field_name}` will be changed." . PHP_EOL;
                $message          .= "From {$fetched_fields[$field_name]['Type']} to " . $model_field->getSqlType() . PHP_EOL;
                $message          .= "Be aware of potential loss of information.";
                $this->warnings[] = $message;

                // adding migration to change field type
                $this->_migrations[] = self::_getChangeColumnStatement($field_name, $model_field->getCreateStatement());
                continue;
            }
        }

        // checking if there're extra fields, just to notify the user, without making any migrations
        foreach ($fetched_fields as $fetched_field) {
            if (!isset($model_fields[$fetched_field['Field']])) {
                $message          = "You have additional field `{$fetched_field['Field']}` in your table." . PHP_EOL;
                $message          .= "Which is not specified in the model. Please be aware that it may impact performance of the table." . PHP_EOL;
                $message          .= "Now you're informed.";
                $this->warnings[] = $message;
            }
        }
    }

    // -------------------------------------------------------
    // Index migrations
    // -------------------------------------------------------

    protected function _checkIndexes()
    {

        $fetched_indexes = $this->_model_class::objects()->getIndexes();
        $index_conf      = self::_makeRealIndexConf($fetched_indexes);
        $real_indexes    = $index_conf['simple_array'];

        // getting model indexes
        $model_indexes = $this->_getModelInstance()->indexes();

        // checking if there're missing indexes
        $this->_checkMissingIndexes($real_indexes, $model_indexes);

        // checking if there're unnecessary indexes
        $this->_checkUnnecessaryIndexes($real_indexes, $model_indexes, $index_conf['index_names']);
    }

    protected function _checkMissingIndexes(array $real_indexes, array $model_indexes)
    {

        foreach ($model_indexes as $index) {
            if (!in_array($index, $real_indexes)) {

                // creating a migration for that index
                $this->_migrations[] = self::_getAddIndexStatement(
                      self::_getIndexName($index),
                      $index
                );
            }
        }
    }

    protected function _checkUnnecessaryIndexes(array $real_indexes, array $model_indexes, array $index_names)
    {

        foreach ($real_indexes as $key => $index) {
            if (!in_array($index, $model_indexes)) {
                $this->_migrations[] = self::_getDropIndexStatement($index_names[$key]);
            }
        }
    }

    // -------------------------------------------------------
    // General Utils
    // -------------------------------------------------------

    protected function _getModelInstance():Model
    {

        if ($this->_model_instance instanceof Model) {
            return $this->_model_instance;
        }

        $this->_model_instance = new $this->_model_class();

        return $this->_model_instance;
    }

    protected function _prepareMigration(string $migration):string
    {

        $table_name = $this->_getModelInstance()->table_name;

        return "ALTER TABLE `{$table_name}` {$migration}";
    }

    // -------------------------------------------------------
    // Column utils
    // -------------------------------------------------------

    protected static function _prepareFetchedFields(array $fetched_fields):array
    {

        $output = [];
        foreach ($fetched_fields as $item) {
            $output[$item['Field']] = $item;
        }

        return $output;
    }

    protected static function _getAddColumnStatement(string $column_name, string $add_statement_from_field):string
    {

        return "ADD `{$column_name}` {$add_statement_from_field}";
    }

    protected static function _getChangeColumnStatement(string $column_name, string $add_statement_from_field):string
    {

        return "CHANGE `{$column_name}` `{$column_name}` {$add_statement_from_field}";
    }

    // -------------------------------------------------------
    // Index Utils
    // -------------------------------------------------------

    protected static function _makeRealIndexConf(array $real_indexes):array
    {

        $output = [];
        foreach ($real_indexes as $index) {

            if ($index['Key_name'] == 'PRIMARY') {
                continue;
            }

            $output[$index['Key_name']][] = $index['Column_name'];
        }

        if (count($output) < 1) {
            return [
                  'simple_array' => [],
                  'index_names'  => [],
            ];
        }

        return [
              'simple_array' => array_values($output),
              'index_names'  => array_keys($output),
        ];
    }

    protected static function _getIndexName(array $fields):string
    {

        $name = '';

        foreach ($fields as $column_name) {
            $name .= $column_name . '_';
        }

        return rtrim($name, '_');
    }

    protected static function _getAddIndexStatement(string $index_name, array $fields)
    {

        $fields_string = '';
        foreach ($fields as $column_name) {
            $fields_string .= "`{$column_name}`, ";
        }
        $fields_string = rtrim($fields_string, ', ');

        return "ADD INDEX `{$index_name}` ({$fields_string});";
    }

    protected static function _getDropIndexStatement(string $index_name)
    {

        return "DROP INDEX `{$index_name}`;";
    }

}
