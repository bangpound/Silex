<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\Routing\LazyRequestMatcher;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * LazyRequestMatcher test case.
 *
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 */
class LazyRequestMatcherTest extends TestCase
{
    /**
     * @covers \Silex\Provider\Routing\LazyRequestMatcher::getRequestMatcher
     */
    public function testUserMatcherIsCreatedLazily()
    {
        $callCounter = 0;
        $requestMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RequestMatcherInterface')->getMock();
        $requestMatcher->method('matchRequest')->willReturn([]);

        $matcher = new LazyRequestMatcher(function () use ($requestMatcher, &$callCounter) {
            ++$callCounter;

            return $requestMatcher;
        });

        $this->assertEquals(0, $callCounter);
        $request = Request::create('path');
        $matcher->matchRequest($request);
        $this->assertEquals(1, $callCounter);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Factory supplied to LazyRequestMatcher must return implementation of Symfony\Component\Routing\RequestMatcherInterface.
     */
    public function testThatCanInjectRequestMatcherOnly()
    {
        $matcher = new LazyRequestMatcher(function () {
            return 'someMatcher';
        });

        $request = Request::create('path');
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Factory supplied to LazyRequestMatcher must return implementation of Symfony\Component\Routing\RequestMatcherInterface.');
        $matcher->matchRequest($request);
    }

    /**
     * @covers \Silex\Provider\Routing\LazyRequestMatcher::matchRequest
     */
    public function testMatchIsProxy()
    {
        $request = Request::create('path');
        /** @var RequestMatcherInterface $matcher */
        $matcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RequestMatcherInterface')->getMock();
        $matcher->expects($this->once())
            ->method('matchRequest')
            ->with($request)
            ->will($this->returnValue(['matcherReturnValue']));

        $matcher = new LazyRequestMatcher(function () use ($matcher) {
            return $matcher;
        });
        $result = $matcher->matchRequest($request);

        $this->assertEquals(['matcherReturnValue'], $result);
    }
}
