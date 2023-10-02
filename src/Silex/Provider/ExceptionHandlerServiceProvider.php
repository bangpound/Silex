<?php

namespace Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex\ExceptionHandler;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExceptionHandlerServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['exception_handler'] = function ($app) {
            return new ExceptionHandler($app['debug'], $app['error_renderer']);
        };

        $app['error_renderer'] = function ($app) {
            return new HtmlErrorRenderer($app['debug'], $app['charset']);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        if (isset($app['exception_handler'])) {
            $dispatcher->addSubscriber($app['exception_handler']);
        }
    }
}
