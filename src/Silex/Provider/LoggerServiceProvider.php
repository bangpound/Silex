<?php

namespace Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex\EventListener\LogListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Log\Logger;

class LoggerServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['logger'] = function ($app) {
            return new Logger($app['logger.level'], $app['logger.output']);
        };

        $pimple['logger.output'] = 'php://stderr';
        $pimple['logger.level'] = null;

        $pimple['logger.listener'] = function ($app) {
            return new LogListener($app['logger'], $app['logger.exception.logger_filter']);
        };

        $pimple['logger.exception.logger_filter'] = null;
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app['logger.listener']);
    }
}
