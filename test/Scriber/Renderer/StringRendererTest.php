<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chronolog\Test\Scriber\Renderer;

use Chronolog\DateTimeStatement;
use Chronolog\LogEntity;
use Chronolog\Scriber\Renderer\StringRenderer;
use Chronolog\Severity;
use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypeError;

/**
 * StringRendererTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 08.05.2024 12:51:01
 */
class StringRendererTest  extends TestCase
{
    public function testRender(): void
    {
        $renderer = new StringRenderer(['format' => 'Y/m/d']);
        $logEntity = new LogEntity(
            new DateTimeStatement('Ymd'),
            Severity::Debug,
            'message text',
            'test',
            ['foo' => 'bar']
        );
        $output = $renderer->render($logEntity);
        $this->assertMatchesRegularExpression('/^\[\d{4}\/\d{2}\/\d{2}\]: test DEBUG message text \{.*\}\n$/', $output);
    }

    public function testRenderWithException(): void
    {
        $renderer = new StringRenderer(['format' => 'Y/m/d']);
        $logEntity = new LogEntity(
            new DateTimeStatement('Ymd'),
            Severity::Debug,
            'message text',
            'test',
            ['foo' => 'bar', new Exception('exception message')],
        );
        $output = $renderer->render($logEntity);
        $this->assertMatchesRegularExpression('/^\[\d{4}\/\d{2}\/\d{2}\]: test DEBUG message text \{.*\}/', $output);
    }

    public function testStringify(): void
    {
        $renderer = new StringRenderer();
        $this->assertEquals('NULL', $renderer->stringify(null));
        $this->assertEquals('true', $renderer->stringify(true));
        $this->assertEquals('1', $renderer->stringify(1));
        $this->assertEquals('foo', $renderer->stringify('foo'));
        $this->assertEquals('{"foo":"bar"}', $renderer->stringify(['foo' => 'bar']));
    }

    public function testGetPattern(): void
    {
        $renderer = new StringRenderer();
        $this->assertEquals(StringRenderer::PATTERN, $renderer->getPattern());
    }

    public function testSetPattern(): void
    {
        $renderer = new StringRenderer();
        $renderer->setPattern('%datetime%');
        $this->assertEquals('%datetime%', $renderer->getPattern());
    }

    public function testFormalizeException(): void
    {
        $renderer = new StringRenderer(['include_traces' => true]);
        $exception = new Exception('Test exception', 123);
        $output = $renderer->formalizeException($exception);
        $this->assertMatchesRegularExpression('/^\[err\] Exception #123: Test exception at .*StringRendererTest\.php:\d+/', $output);
    }

    public function testFormalizeTrace(): void
    {
        $renderer = new StringRenderer();
        $trace = debug_backtrace();
        $output = $renderer->formalizeTrace($trace);
        $this->assertMatchesRegularExpression('/^\[backtrace\]\n\#\d+/', $output);
    }
}
/** End of StringRendererTest **/
