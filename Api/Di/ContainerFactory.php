<?php

namespace Api\Di;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class ContainerFactory
{
    const CONFIG_PATH = __DIR__ . '/config/';
    const GENERAL_CONFIG_FILENAME = 'general.php';

    public static function create(bool $from_config = true):Container {
        if (!$from_config) {
            return new Container();
        }

        $container = new Container();

        // loading config
        $services = self::_getServiceList();

        foreach ($services as $service_name => $service_item) {

            $container->register($service_name, $service_item['class']);

            //
            if (isset($service_item['arguments'])) {
                foreach ($service_item['arguments'] as $argument) {
                    $container->addArgument($service_name, $argument);
                }
            }
        }

        // creating services and parameters
        return $container;
    }

    // -------------------------------------------------------
    // Protected
    // -------------------------------------------------------

    protected static function _getServiceList() {
        $file_path = self::CONFIG_PATH . self::GENERAL_CONFIG_FILENAME;

        if (!file_exists($file_path)) {
            throw new \RuntimeException('Config file for DI was not found');
        }

        //
        $config_content = require_once $file_path;

        if (!isset($config_content['services'])) {
            throw new \RuntimeException('Config file for DI does not have services');
        }

        return $config_content['services'];
    }
}
