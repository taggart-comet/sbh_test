<?php

namespace Api\Di\Exception;

use Psr\Container\ContainerExceptionInterface;

class ServiceNotFoundException extends \Exception implements ContainerExceptionInterface
{

}
