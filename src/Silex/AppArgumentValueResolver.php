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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * HttpKernel Argument Resolver for Silex.
 *
 * @author Romain Neutron <imprec@gmail.com>
 */
class AppArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(private Application $app)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (null !== $argument->getType() && (Application::class === $argument->getType() || is_subclass_of($argument->getType(), Application::class))) {
            return [$this->app];
        }

        return [];
    }
}
