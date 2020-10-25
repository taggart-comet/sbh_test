<?php

namespace Api;

use Zend\Diactoros\Request;

/**
 * Class Runner
 * @package Api
 *   Finds the right endpoint for the request
 *   and runs them via Checker
 */

class Runner {

    // finds the right endpoint for the request
    public static function work(string $resource, array $endpoint_list, Request $request):array
    {

        // there can be only 2 more end points
        if (count($endpoint_list) > 2) {
            return Handler::error(405, 'Invalid request uri');
        }

        $resource_class = 'Api\\Controllers\\' . ucfirst($resource);
        if (!class_exists($resource_class)) {
            return Handler::error(405, 'Invalid resource requested');
        }

        // checking on `getAll`
        if (count($endpoint_list) < 1) {

            // now checking if the resource can be fetched all
            if (!in_array(Handler::RESOURCE_ALL, $resource_class::ALLOWED_REQUESTS)) {
                return Handler::error(405, 'Invalid way of accessing resources');
            }

            // executing `getAll` it's an agreed name
            return Checker::work($resource_class, 'getAll', $request);
        }

        // if second part is action
        if (count($endpoint_list) == 1) {

            $action = self::_transformToMethodName($endpoint_list[0]);

            // now checking if the resource can be fetched all
            if (!isset($resource_class::ALLOWED_ACTIONS[$action])) {
                return Handler::error(405, 'Invalid request action');
            }

            // executing action
            return Checker::work($resource_class, $action, $request);
        }

        // working with action on by resource id
        // if int
        if (is_numeric($endpoint_list[0]) && in_array(Handler::RESOURCE_ID_INT, $resource_class::ALLOWED_REQUESTS)) {

            $action = self::_transformToMethodName($endpoint_list[1]);

            if (isset($resource_class::ALLOWED_ID_ACTIONS[$action])) {
                return Checker::work($resource_class, $action, $request, $endpoint_list[0]);
            }
        }

        // if string
        if (in_array(Handler::RESOURCE_ID_STRING, $resource_class::ALLOWED_REQUESTS)) {

            $action = self::_transformToMethodName($endpoint_list[1]);

            if (isset($resource_class::ALLOWED_ID_ACTIONS[$action])) {
                return Checker::work($resource_class, $action, $request, $endpoint_list[0]);
            }
        }

        // if more than three api levels will be required they can be added here..

        return Handler::error(405, 'Invalid action for the resource');
    }

    // -------------------------------------------------------
    // Utils
    // -------------------------------------------------------

    // changes a string from change-status to changeStatus
    protected static function _transformToMethodName(string $endpoint):string
    {

        $tt = explode('-', $endpoint);

        if (!isset($tt[1])) {

            return $endpoint;
        }

        $method = '';
        foreach ($tt as $part) {

            if ($method == '') {
                $method .= $part;
            } else {
                $method .= ucfirst($part);
            }
        }

        return $method;
    }
}
