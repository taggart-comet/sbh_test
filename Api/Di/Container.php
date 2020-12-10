<?php

namespace Api\Di;

use Api\Di\Exception\ContainerException;
use Api\Di\Exception\ParameterNotFoundException;
use Api\Di\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{

    private array $registered_services = [];
    private array $arguments           = [];
    private array $active_services     = [];

    // -------------------------------------------------------
    // PUBLIC
    // -------------------------------------------------------

    // a psr method
    public function get($service_name)
    {

        if (!$this->has($service_name)) {
            throw new ServiceNotFoundException('Service not found: ' . $service_name);
        }

        if (isset($this->active_services[$service_name])) {
            return $this->active_services[$service_name];
        }

        $this->active_services[$service_name] = $this->_createService($service_name);
        return $this->active_services[$service_name];
    }

    // a psr method
    public function has($service_name):bool
    {

        return isset($this->registered_services[$service_name]);
    }

    //
    public function register(string $service_name, string $full_class_name) {
        $this->registered_services[$service_name] = $full_class_name;
        $this->arguments[$service_name]           = [];
    }

    //
    public function addArgument(string $service_name, string $argument) {
        if (!isset($this->arguments[$service_name])) {
            $this->arguments[$service_name] = [];
        }

        $this->arguments[$service_name][] = $argument;
    }

    // -------------------------------------------------------
    // PROTECTED
    // -------------------------------------------------------

    protected function _createService(string $service_name)
    {
        $full_class_name = $this->registered_services[$service_name];

        if (!class_exists($full_class_name)) {
            throw new ContainerException($service_name.' service class does not exist');
        }

        $arguments = [];

        foreach ($this->arguments[$service_name] as $argument_item) {
            $arguments[] = $this->_resolveArgument($argument_item, $service_name);
        }

        $reflector = new \ReflectionClass($full_class_name);
        return $reflector->newInstanceArgs($arguments);
    }

    // if we have `@` at the start of the string - the we need to instantiate this argument
    // as a service
    protected function _resolveArgument(string $argument_name, string $for_service) {

        if (!preg_match('/^@.*/', $argument_name)) {
            return $argument_name;
        }

        //
        $service_name = trim($argument_name, '@');

        if ($service_name == $for_service) {
            throw new ContainerException('Service depends on itself');
        }

        return $this->_createService($service_name);
    }

}
