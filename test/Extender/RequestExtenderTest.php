<?php
/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 namespace Chronolog\Test\Extenders;

use Chronolog\Extender\RequestExtender;
use PHPUnit\Framework\TestCase;
/**
 * RequestExtenderTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 17.05.2024 11:06:00
 */
class RequestExtenderTest extends TestCase
{
    private $requestExtender;

    protected function setUp(): void
    {
        $this->requestExtender = new RequestExtender();
    }

    public function testGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('POST', $this->requestExtender->getMethod());
    }

    public function testGetRequestType()
    {
        $this->assertContains($this->requestExtender->getRequestType(), [RequestExtender::REQUEST_CLI, RequestExtender::REQUEST_HTTP, RequestExtender::REQUEST_AJAX]);
    }

    public function testGetUserAgent()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
        $this->assertEquals('Mozilla/5.0', $this->requestExtender->getUserAgent());
    }

    public function testGetUserIp()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->assertEquals('127.0.0.1', $this->requestExtender->getUserIP());
    }

    public function testGetUri()
    {
        $_SERVER['REQUEST_URI'] = '/test';
        $this->assertEquals('/test', $this->requestExtender->getUri());
    }
}
/** End of RequestExtenderTest **/