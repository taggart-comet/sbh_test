<?php

namespace Api;

/**
 * Class Validator
 * @package Api
 *          Validates data sent by clients
 *          Called from controllers
 */
class Validator
{

    public static function string(array $data, string $key, $obligatory = true, int $length = null)
    {

        # trimming and escaping
        try {
            $string = self::_formatString(self::_getParam($data, $key));
        } catch (\InvalidArgumentException $e) {
            if ($obligatory) {
                throw $e;
            }
            return null;
        }

        # checking its length if needed
        if (!is_null($length) && strlen($string) != $length) {
            throw new \InvalidArgumentException("Argument is of invalid length");
        }

        return $string;
    }

    public static function int(
          array $data,
          string $key,
          $obligatory = true,
          int $limit_bottom = null,
          int $limit_top = null
    ) {

        # trimming and escaping
        try {
            $int = self::_formatInt(self::_getParam($data, $key));
        } catch (\InvalidArgumentException $e) {
            if ($obligatory) {
                throw $e;
            }
            return null;
        }

        # checking bottom limit
        if (!is_null($limit_bottom)) {
            if ($int < $limit_bottom) {
                throw new \InvalidArgumentException("Argument is of invalid value");
            }
        }

        # checking upper limit
        if (!is_null($limit_top)) {
            if ($int > $limit_top) {
                throw new \InvalidArgumentException("Argument is of invalid value");
            }
        }

        return $int;
    }

    public static function phoneNumber(array $data, string $key):string
    {

        $string = self::_formatString(self::_getParam($data, $key));

        if (!preg_match('/^\+?(\d{1,2}?)[\s.-]?\(?(\d{3})\)?[ .-]?(\d{3})[ .-]?(\d{4})$/', $string, $matches)) {
            throw new \InvalidArgumentException("Argument is of invalid value");
        }

        return $matches[1].$matches[2].$matches[3].$matches[4];
    }

    // -------------------------------------------------------
    // Utils
    // -------------------------------------------------------

    protected static function _getParam(array $data, string $key)
    {

        if (!isset($data[$key])) {
            throw new \InvalidArgumentException("Arguments were not passed");
        }

        return $data[$key];
    }

    // removes all non UTF chars
    // https://stackoverflow.com/questions/1401317/remove-non-utf8-characters-from-string
    protected static function _formatString(string $value):string
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

    //
    protected static function _formatInt($value):int
    {

        $value = trim($value);
        $value = str_replace(",", ".", $value);
        $value = preg_replace("#[^0-9\.-]*#ism", "", $value);
        return intval($value);
    }
}

