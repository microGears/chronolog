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
use Chronolog\LogEntity;
use Chronolog\DateTimeStatement;
use Chronolog\Severity;
use PHPUnit\Framework\TestCase;
/**
 * RequestExtenderTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 17.05.2024 11:06:00
 */
class RequestExtenderTest extends TestCase
{
    private $extender;

    protected function setUp(): void
    {
        $this->extender = new RequestExtender();
    }

    public function testInvoke()
    {
        $extender = new RequestExtender();
        $entity = new LogEntity(new DateTimeStatement(), Severity::Info, "Test for __invoke", 'test');

        $result = $extender($entity);
        $this->assertInstanceOf(LogEntity::class, $result);
        $this->assertArrayHasKey('request', $result->assets);
    }

    public function testGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('POST', $this->extender->getMethod());
    }

    public function testGetRequestType()
    {
        $this->assertContains($this->extender->getRequestType(), [RequestExtender::REQUEST_CLI, RequestExtender::REQUEST_HTTP, RequestExtender::REQUEST_AJAX]);
    }

    public function testGetUserAgent()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
        $this->assertEquals('Mozilla/5.0', $this->extender->getUserAgent());
    }

    public function testGetUserIp()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->assertEquals('127.0.0.1', $this->extender->getUserIP());
    }

    public function testGetUri()
    {
        $_SERVER['REQUEST_URI'] = '/test';
        $this->assertEquals('/test', $this->extender->getUri());
    }
}
/** End of RequestExtenderTest **/