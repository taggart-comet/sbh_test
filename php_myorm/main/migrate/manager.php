<?php

namespace PhpMyOrm\migrate;

use PhpMyOrm\Model;

// checks table on structure and indexes
// form a list of migrations then asks user
// if he's okay to implement this list right now,
// knowing that he can potentially loose hist data
// or get hist table locked for some time (depending on it's size)

class MigrateManager
{

    const CONFIG_FIELD = 'MODEL_CLASSES';

    // log level
    const INFO    = 0;
    const SUCCESS = 1;
    const WARNING = 2;
    const ERROR   = 3;

    protected array $enabled_models = [];

    public function __construct()
    {

        $conf = require __DIR__ . '/../../conf.php';

        if (!isset($conf[self::CONFIG_FIELD])) {
            throw new ConfigFormatError('Field: `' . self::CONFIG_FIELD . ' ` is not specified in the configuration file.');
        }

        $this->enabled_models = $conf[self::CONFIG_FIELD];
    }

    public function handle(array $args)
    {

        if (count($args) < 1) {
            foreach ($this->enabled_models as $model_class) {
                $this->checkModel($model_class);
            }
            return;
        }

        // silently when we need only to create tables
        if (count($args) == 1 && isset($args[1]) && $args[1] == '--create-tables') {

            foreach ($args as $model_class) {
                if (in_array($model_class, $this->enabled_models)) {
                    $this->checkModel($model_class, true);
                }
            }
            return;
        }

        // overall check
        foreach ($args as $model_class) {

            if (in_array($model_class, $this->enabled_models)) {
                $this->checkModel($model_class);
            } else {
                self::_log("Argument `{$model_class}` was skipped, as not present in the config", self::WARNING);
            }
        }
    }

    protected function checkModel($model_class, $only_create = false)
    {

        // checking first that paths to model classes typed correctly in the config
        try {
            new $model_class();
        } catch (\Error $e) {
            self::_log("Class `{$model_class}` was not found! Make sure to include your classes for this script!",
                  self::ERROR);
            return;
        }

        $migrate = new MigrateMain($model_class);

        // only creating silently
        if ($only_create) {
            try {
                $migrate->check();;
            } catch (NeedToCreateTableException $e) {
                $migrate->createTable();
            } catch (NeedToMigrateException $e) {

            }
            return;
        }

        // checking if the table exists
        try {
            $migrate->check();
        } catch (NeedToCreateTableException $e) {
            self::_log("Creating table for class `{$model_class}`");
            $migrate->createTable();

            if (count($migrate->warnings) > 0) {
                foreach ($migrate->warnings as $warning_text) {
                    self::_log($warning_text, self::WARNING);
                }
            }

            return;
        } catch (NeedToMigrateException $e) {

            // if table that exists differs from the model setup - suggesting to ALTER the table
            self::_log("Table structure differs from the model `{$model_class}`", self::WARNING);

            // displaying migrations and warning
            foreach ($e->migrations as $migration_sql) {
                self::_log($migration_sql);
            }
            foreach ($e->warnings as $warning_text) {
                self::_log($warning_text, self::WARNING);
            }

            // asking user if he wishes to apply migrations
            if (self::_isUserInputExpected('These migrations will be implemented!', 'yes')) {

                self::_log("Running migrations.. ", self::WARNING);

                // apply migrations to the table
                $migrate->runMigrations();

                self::_log("Table for class `{$model_class}` is present and up to date", self::SUCCESS);
            } else {
                self::_log("The table is not in sync with the model .. ", self::ERROR);
            }

            return;
        }

        if (count($migrate->warnings) > 0) {
            foreach ($migrate->warnings as $warning_text) {
                self::_log($warning_text, self::WARNING);
            }
        }

        self::_log("Table for class `{$model_class}` is present and up to date", self::SUCCESS);
    }

    // -------------------------------------------------------
    // PROTECTED
    // -------------------------------------------------------

    // -------------------------------------------------------
    // STDIN
    // -------------------------------------------------------

    // waits for user to type in the response
    protected static function _isUserInputExpected(string $message, string $expected_answer):bool
    {

        echo self::_purpleText('[ACTION REQUIRED]: ') . $message . PHP_EOL;
        $message = " - Enter [{$expected_answer}] to confirm: ";

        $line = readline($message);

        if ($line == $expected_answer) {
            return true;
        }

        return false;
    }

    // -------------------------------------------------------
    // STDOUT
    // -------------------------------------------------------

    protected static function _log($message, $level = self::INFO)
    {

        switch ($level) {
            case self::INFO:
                $message = self::_blueText('[INFO]: ') . $message;
                echo $message . PHP_EOL;
                break;
            case self::SUCCESS:
                $message = self::_greenText('[SUCCESS]: ') . $message;
                echo $message . PHP_EOL;
                break;
            case self::WARNING:
                $message = self::_yellowText('[WARNING]: ') . $message;
                echo $message . PHP_EOL;
                break;
            case self::ERROR:
                $message = self::_redText('[ERROR]: ') . $message;
                echo $message . PHP_EOL;
                break;
            default:
                break;
        }
    }

    protected static function _redText(string $text, bool $underline = false):string
    {

        return self::_getCodedTextForCli($text, 31, $underline);
    }

    protected static function _greenText(string $text, bool $underline = false):string
    {

        return self::_getCodedTextForCli($text, 32, $underline);
    }

    protected static function _yellowText(string $text, bool $underline = false):string
    {

        return self::_getCodedTextForCli($text, 33, $underline);
    }

    protected static function _blueText(string $text, bool $underline = false):string
    {

        return self::_getCodedTextForCli($text, 96, $underline);
    }

    protected static function _purpleText($text, $underline = false)
    {

        return self::_getCodedTextForCli($text, 35, $underline);
    }

    protected static function _getCodedTextForCli(string $text, int $code = 0, bool $underline = false):string
    {

        $str = "\033[{$code}m{$text}\033[0m";

        if ($underline) {
            $str = "\033[{$code}m\e[4m{$text}\033[0m";
        }

        return $str;
    }
}
