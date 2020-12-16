<?php

namespace Api\Di;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ContainerFactory
{

    const CONFIG_PATH_DUMMY             = __DIR__ . '/config/dummy/';
    const CONFIG_PATH_SYMFONY             = __DIR__ . '/config/symfony/';
    const GENERAL_CONFIG_FILENAME = 'general.php';

    const CONTAINER_TYPE_DUMMY   = 0;
    const CONTAINER_TYPE_SYMFONY = 1;

    public static function create(int $container_type, bool $from_config = true):ContainerInterface
    {
       switch ($container_type) {
           case self::CONTAINER_TYPE_DUMMY:
               return self::_createDummyContainer($from_config);
           case self::CONTAINER_TYPE_SYMFONY:
               return self::_createSymfonyContainer();
           default:
               throw new \Exception();
       }
    }

    // -------------------------------------------------------
    // Protected
    // -------------------------------------------------------

    protected static function _createDummyContainer(bool $from_config):ContainerInterface {
        if (!$from_config) {
            return new Container();
        }

        $container = new Container();

        // loading config
        $services = self::_getDummyServiceList();

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

    protected static function _createSymfonyContainer():ContainerInterface {

        $symfony_container = new ContainerBuilder();

        //
        $loader = new PhpFileLoader($symfony_container, new FileLocator(__DIR__));
        $loader->load(self::CONFIG_PATH_SYMFONY . self::GENERAL_CONFIG_FILENAME);

        $symfony_container->compile();

        return $symfony_container;
    }

    protected static function _getDummyServiceList()
    {

        $file_path = self::CONFIG_PATH_DUMMY . self::GENERAL_CONFIG_FILENAME;

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
