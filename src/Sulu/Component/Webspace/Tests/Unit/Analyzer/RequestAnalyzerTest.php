<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Webspace\Tests\Unit\Analyzer;

use Prophecy\Argument;
use Sulu\Component\Webspace\Analyzer\Attributes\RequestAttributes;
use Sulu\Component\Webspace\Analyzer\Attributes\RequestProcessorInterface;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestAnalyzerTest extends \PHPUnit_Framework_TestCase
{
    public function testAnalyze()
    {
        $provider = $this->prophesize(RequestProcessorInterface::class);
        $request = $this->prophesize(Request::class);

        $provider->process($request->reveal(), Argument::type(RequestAttributes::class))
            ->shouldBeCalled()->willReturn(new RequestAttributes());
        $provider->validate(Argument::type(RequestAttributes::class))->shouldBeCalled()->willReturn(true);

        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getCurrentRequest()->willReturn($request->reveal());
        $requestAnalyzer = new RequestAnalyzer($requestStack->reveal(), [$provider->reveal()]);
        $requestAnalyzer->analyze($request->reveal());
    }

    public function testGetAttribute()
    {
        $provider = $this->prophesize(RequestProcessorInterface::class);
        $request = $this->prophesize(Request::class);

        $provider->process($request->reveal(), Argument::type(RequestAttributes::class))
            ->shouldBeCalledTimes(1)->willReturn(new RequestAttributes(['test' => 1]));
        $provider->validate(Argument::type(RequestAttributes::class))->shouldBeCalled()->willReturn(true);

        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getCurrentRequest()->willReturn($request->reveal());
        $requestAnalyzer = new RequestAnalyzer($requestStack->reveal(), [$provider->reveal()]);

        $this->assertEquals(1, $requestAnalyzer->getAttribute('test'));
        $this->assertEquals(2, $requestAnalyzer->getAttribute('test1', 2));
    }

    public function testGetAttributeTwice()
    {
        $provider = $this->prophesize(RequestProcessorInterface::class);
        $request = $this->prophesize(Request::class);

        $provider->process($request->reveal(), Argument::type(RequestAttributes::class))
            ->shouldBeCalledTimes(1)->willReturn(new RequestAttributes(['test' => 1]));
        $provider->validate(Argument::type(RequestAttributes::class))->shouldBeCalled()->willReturn(true);

        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getCurrentRequest()->willReturn($request->reveal());
        $requestAnalyzer = new RequestAnalyzer($requestStack->reveal(), [$provider->reveal()]);

        $this->assertEquals(1, $requestAnalyzer->getAttribute('test'));
        $this->assertEquals(2, $requestAnalyzer->getAttribute('test1', 2));

        $this->assertEquals(1, $requestAnalyzer->getAttribute('test'));
        $this->assertEquals(2, $requestAnalyzer->getAttribute('test1', 2));
    }

    public function testAnalyzeMultipleProvider()
    {
        $provider1 = $this->prophesize(RequestProcessorInterface::class);
        $provider2 = $this->prophesize(RequestProcessorInterface::class);
        $request = $this->prophesize(Request::class);

        $provider1->process($request->reveal(), Argument::type(RequestAttributes::class))->shouldBeCalled()->willReturn(
            new RequestAttributes(['test1' => 1])
        );
        $provider1->validate(Argument::type(RequestAttributes::class))->shouldBeCalled()->willReturn(true);
        $provider2->process($request->reveal(), Argument::type(RequestAttributes::class))->shouldBeCalled()->willReturn(
            new RequestAttributes(['test1' => 2, 'test2' => 3])
        );
        $provider2->validate(Argument::type(RequestAttributes::class))->shouldBeCalled()->willReturn(true);

        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getCurrentRequest()->willReturn($request->reveal());
        $requestAnalyzer = new RequestAnalyzer($requestStack->reveal(), [$provider1->reveal(), $provider2->reveal()]);

        $requestAnalyzer->analyze($request->reveal());

        $this->assertEquals(2, $requestAnalyzer->getAttribute('test1'));
        $this->assertEquals(3, $requestAnalyzer->getAttribute('test2'));
        $this->assertNull($requestAnalyzer->getAttribute('test3'));
    }

    public function provideGetter()
    {
        return [
            [['matchType' => 1], 'getMatchType', 1],
            [[], 'getMatchType', null],
            [['webspace' => 1], 'getWebspace', 1],
            [[], 'getWebspace', null],
            [['portal' => 1], 'getPortal', 1],
            [[], 'getPortal', null],
            [['segment' => 1], 'getSegment', 1],
            [[], 'getSegment', null],
            [['localization' => 1], 'getCurrentLocalization', 1],
            [[], 'getCurrentLocalization', null],
            [['portalUrl' => 1], 'getPortalUrl', 1],
            [[], 'getPortalUrl', null],
            [['redirect' => 1], 'getRedirect', 1],
            [[], 'getRedirect', null],
            [['resourceLocator' => 1], 'getResourceLocator', 1],
            [[], 'getResourceLocator', null],
            [['resourceLocatorPrefix' => 1], 'getResourceLocatorPrefix', 1],
            [[], 'getResourceLocatorPrefix', null],
            [['postParameter' => 1], 'getPostParameters', 1],
            [[], 'getPostParameters', []],
            [['getParameter' => 1], 'getGetParameters', 1],
            [[], 'getGetParameters', []],
            [['analyticsKey' => 1], 'getAnalyticsKey', 1],
            [[], 'getAnalyticsKey', ''],
            [['portalInformation' => 1], 'getPortalInformation', 1],
            [[], 'getPortalInformation', null],
        ];
    }

    /**
     * @dataProvider provideGetter
     */
    public function testGetter(array $attributes, $method, $expected)
    {
        $provider = $this->prophesize(RequestProcessorInterface::class);
        $request = $this->prophesize(Request::class);
        $provider->process($request->reveal(), Argument::type(RequestAttributes::class))
            ->shouldBeCalled()->willReturn(new RequestAttributes($attributes));
        $provider->validate(Argument::type(RequestAttributes::class))->shouldBeCalled()->willReturn(true);

        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getCurrentRequest()->willReturn($request->reveal());
        $requestAnalyzer = new RequestAnalyzer($requestStack->reveal(), [$provider->reveal()]);
        $requestAnalyzer->analyze($request->reveal());

        $this->assertEquals($expected, $requestAnalyzer->{$method}());
    }
}