<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Silex\EventListener\LogListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * LogListener.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
class LogListenerTest extends TestCase
{
    public function testRequestListener()
    {
        $logger = $this->getMockBuilder('Psr\\Log\\LoggerInterface')->getMock();
        $logger
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::DEBUG, '> GET /foo')
        ;

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new LogListener($logger));

        $kernel = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\HttpKernelInterface')->getMock();

        $dispatcher->dispatch(new GetResponseEvent($kernel, Request::create('/subrequest'), HttpKernelInterface::SUB_REQUEST), KernelEvents::REQUEST);

        $dispatcher->dispatch(new GetResponseEvent($kernel, Request::create('/foo'), HttpKernelInterface::MASTER_REQUEST), KernelEvents::REQUEST);
    }

    public function testResponseListener()
    {
        $logger = $this->getMockBuilder('Psr\\Log\\LoggerInterface')->getMock();
        $logger
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::DEBUG, '< 301')
        ;

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new LogListener($logger));

        $kernel = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\HttpKernelInterface')->getMock();

        $dispatcher->dispatch(new FilterResponseEvent($kernel, Request::create('/foo'), HttpKernelInterface::SUB_REQUEST, Response::create('subrequest', 200)), KernelEvents::RESPONSE);

        $dispatcher->dispatch(new FilterResponseEvent($kernel, Request::create('/foo'), HttpKernelInterface::MASTER_REQUEST, Response::create('bar', 301)), KernelEvents::RESPONSE);
    }

    public function testExceptionListener()
    {
        $logger = $this->getMockBuilder('Psr\\Log\\LoggerInterface')->getMock();
        $logger
            ->expects($this->exactly(2))
            ->method('log')
            ->withConsecutive(
                [LogLevel::CRITICAL, 'RuntimeException: Fatal error (uncaught exception) at '.__FILE__.' line '.(__LINE__ + 10)],
                [LogLevel::ERROR, 'Symfony\Component\HttpKernel\Exception\HttpException: Http error (uncaught exception) at '.__FILE__.' line '.(__LINE__ + 10)]
            );


        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new LogListener($logger));

        $kernel = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\HttpKernelInterface')->getMock();

        $dispatcher->dispatch(new GetResponseForExceptionEvent($kernel, Request::create('/foo'), HttpKernelInterface::SUB_REQUEST, new \RuntimeException('Fatal error')), KernelEvents::EXCEPTION);
        $dispatcher->dispatch(new GetResponseForExceptionEvent($kernel, Request::create('/foo'), HttpKernelInterface::SUB_REQUEST, new HttpException(400, 'Http error')), KernelEvents::EXCEPTION);
    }
}
