<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



namespace Chronolog\Test\Scriber;

use Chronolog\DateTimeStatement;
use Chronolog\LogEntity;
use Chronolog\Scriber\ErrorLogScriber;
use Chronolog\Scriber\Renderer\StringRenderer;
use Chronolog\Severity;
use Chronolog\Scriber\SyslogScriber;
use PHPUnit\Framework\TestCase;

/**
 * ErrorLogScriberTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 13.05.2024 19:33:11
 */
class ErrorLogScriberTest extends TestCase
{
    public function testConstructor()
    {
        $scriber = new ErrorLogScriber();
        $this->assertInstanceOf('Chronolog\Scriber\ErrorLogScriber', $scriber);

        $scriber = new ErrorLogScriber(['severity' => Severity::Debug]);
        $this->assertInstanceOf('Chronolog\Scriber\ErrorLogScriber', $scriber);
    }

    public function testConstructorViaFactory()
    {
        $scriber = ErrorLogScriber::createInstance();
        $this->assertInstanceOf('Chronolog\Scriber\ErrorLogScriber', $scriber);

        $scriber = ErrorLogScriber::createInstance(Severity::Debug);
        $this->assertInstanceOf('Chronolog\Scriber\ErrorLogScriber', $scriber);        
    }

    public function testIsAllowedSeverity()
    {
        $scriber1 = new ErrorLogScriber(['severity' => Severity::Error]);
        $scriber2 = new ErrorLogScriber(['severity' => [Severity::Debug, Severity::Info]]);

        $record = new LogEntity(new DateTimeStatement(), Severity::Error, "Simple message", "test");

        $this->assertTrue($scriber1->isAllowedSeverity($record));
        $this->assertFalse($scriber2->isAllowedSeverity($record));
    }

    public function testWriteToSystem()
    {
        $scriber = new ErrorLogScriber([
            'severity' => Severity::Error,
            'renderer' => new StringRenderer([
                'pattern' => "%severity_name% %track%: %message% %assets%\n",
                'allow_multiline' => true,
                'include_traces' => true,
                'base_path' => dirname(__DIR__,2).'/src'
            ])
        ]);
        $record = new LogEntity(new DateTimeStatement(), Severity::Error, "Simple message", "test", ['exception' => new \Exception('Test exception',1)]);
        $this->assertTrue($scriber->handle($record));
    }
    public function testWriteToEmail()
    {
        $scriber = new ErrorLogScriber([
            'severity' => Severity::Error,
            'renderer' => new StringRenderer([
                'pattern' => "[%datetime%]: %track% %severity_name% %message% %assets%\n",
                'allow_multiline' => true,
                'include_traces' => true,
                'base_path' => dirname(__DIR__,2).'/src'
            ]),
            'message_type' => ErrorLogScriber::MSG_EMAIL,
            'destination' => 'user@mail.com',
            'headers' => 'From: admin@mail.com\r\n'
        ]);
        $record = new LogEntity(new DateTimeStatement(), Severity::Error, "Simple message", "test", ['exception' => new \Exception('Test exception',2)]);
        $this->assertTrue($scriber->handle($record));
    }
    public function testWriteToFile()
    {
        $scriber = new ErrorLogScriber([
            'severity' => Severity::Error,
            'renderer' => new StringRenderer([
                'pattern' => "%datetime%~%track%~%severity_name%~%message%~%assets%\n",
                'allow_multiline' => false,
                'include_traces' => false,
                'base_path' => dirname(__DIR__,2).'/src'
            ]),
            'message_type' => ErrorLogScriber::MSG_FILE,
            'destination' => dirname(__DIR__,2) . '/runtime/logs/'.basename(__FILE__,'.php').'.log'
        ]);
        $record = new LogEntity(new DateTimeStatement(), Severity::Error, "Simple message", "test", ['exception' => new \Exception('Test exception',3)]);
        $this->assertTrue($scriber->handle($record));
    }
}
/** End of ErrorLogScriberTest **/
