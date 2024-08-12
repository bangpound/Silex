<?php

namespace Silex\Provider;

use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class PsrServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple[ContainerInterface::class] = function ($app) {
            return new Psr11Container($app);
        };
        $pimple[EventDispatcherInterface::class] = function ($app) {
            return $app['dispatcher'];
        };
        $pimple[LoggerInterface::class] = function ($app) {
            return $app['logger'];
        };
    }
}
