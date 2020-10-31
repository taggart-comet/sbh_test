<?php

namespace Api;

use Ttask\Application\Exception\GeneralException;
use Zend\Diactoros\Request;

/**
 * Class Checker
 * @package Api
 *    Serves as a decorator over any api-method, can be used to add additional checks, stats, throttling, etc
 */
class Checker
{

    public static function work(string $class_name, string $function_name, Request $request, $resource_id = null):array
    {

        // checking if a method is called properly (GET, PATCH)
        if (!self::isMethodIsCorrect($class_name, $function_name, $request->getMethod())) {
            return Handler::error(405, 'Invalid method used');
        }

        // retrieving request data
        $request_data = json_decode($request->getBody()->getContents(), true);
        if (is_null($request_data)) {
            $request_data = [];
        }

        // adding resource id if needed
        if (!is_null($resource_id)) {
            $request_data['resource_id'] = $resource_id;
        }

        // adding GET params to request_data
        if ($request->getMethod() == Handler::METHOD_GET) {
            parse_str($request->getUri()->getQuery(), $get_params);
            $request_data = array_merge($request_data, $get_params);
        }

        // executing
        try {

            $result = call_user_func($class_name . '::' . $function_name, $request_data);
        } catch (GeneralException $e) {
            return Handler::error($e->getCode(), $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return Handler::error(405, "Invalid params");
        }

        return $result;
    }

    // -------------------------------------------------------
    // Checks
    // -------------------------------------------------------

    public static function isMethodIsCorrect(string $class_name, string $function_name, string $called_method):bool
    {

        if (isset($class_name::ALLOWED_ACTIONS[$function_name]) && $class_name::ALLOWED_ACTIONS[$function_name] == $called_method) {
            return true;
        }

        if (isset($class_name::ALLOWED_ID_ACTIONS[$function_name]) && $class_name::ALLOWED_ID_ACTIONS[$function_name] == $called_method) {
            return true;
        }

        return false;
    }
}
