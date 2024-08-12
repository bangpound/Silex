<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex\Provider;

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex\Provider\Session\SessionFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorageFactory;
use Symfony\Component\HttpKernel\EventListener\SessionListener;

/**
 * Symfony HttpFoundation component Provider for sessions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SessionServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    public function register(Container $app)
    {
        $app['session.test'] = false;

        $app['session'] = function ($app) {
            return $app['session.factory']->createSession();
        };

        $app['session.factory'] = function ($app) {
            return new SessionFactory($app['request_stack'], $app['session.storage.factory'], null, $app['session.attribute_bag'], $app['session.flash_bag']);
        };

        $app['session.storage'] = function ($app) {
            return $app['session.storage.factory']->createStorage();
        };

        $app['session.storage.handler'] = function ($app) {
            return new NativeFileSessionHandler($app['session.storage.save_path']);
        };

        $app['session.storage.factory'] = function ($app) {
            return $app['session.test'] ? $app['session.storage.test.factory'] : $app['session.storage.native.factory'];
        };

        $app['session.storage.native.factory'] = function ($app) {
            return new NativeSessionStorageFactory(
                $app['session.storage.options'],
                $app['session.storage.handler']
            );
        };

        $app['session.storage.test.factory'] = function ($app) {
            return new MockFileSessionStorageFactory($app['session.storage.save_path']);
        };

        $app['session.listener'] = function ($app) {
            return new SessionListener(new ServiceLocator($app, [
                'session_factory' => 'session.factory',
                'logger' => 'logger',
            ]));
        };

        $app['session.storage.options'] = [];
        $app['session.storage.save_path'] = null;
        $app['session.attribute_bag'] = null;
        $app['session.flash_bag'] = null;
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app['session.listener']);
    }
}
