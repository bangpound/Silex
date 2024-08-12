<?php

namespace Silex\Provider\Session;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageFactoryInterface;

class SessionFactory implements SessionFactoryInterface
{
    private $usageReporter;

    public function __construct(
        private readonly RequestStack                   $requestStack,
        private readonly SessionStorageFactoryInterface $storageFactory,
        ?callable                                       $usageReporter = null,
        private readonly ?AttributeBagInterface         $attributes = null,
        private readonly ?FlashBagInterface             $flashes = null
    )
    {
        $this->usageReporter = $usageReporter;
    }

    public function createSession(): SessionInterface
    {
        return new Session($this->storageFactory->createStorage($this->requestStack->getMainRequest()), $this->attributes, $this->flashes, $this->usageReporter);
    }
}

