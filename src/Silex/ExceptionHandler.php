<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex;

use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Default exception handler.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExceptionHandler implements EventSubscriberInterface
{
    protected $debug;

    /**
     * @var ErrorRendererInterface
     */
    private $errorRenderer;

    public function __construct($debug, ErrorRendererInterface $errorRenderer)
    {
        $this->debug = $debug;
        $this->errorRenderer = $errorRenderer;
    }

    public function onSilexError(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $rendered = $this->errorRenderer->render($exception);

        $response = (new Response($rendered->getAsString(), $rendered->getStatusCode(), $rendered->getHeaders()))
            ->setCharset(ini_get('default_charset'));

        $event->setResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onSilexError', -255]];
    }
}
