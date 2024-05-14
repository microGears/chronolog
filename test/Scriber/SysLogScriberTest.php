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
use Chronolog\Scriber\Renderer\StringRenderer;
use Chronolog\Severity;
use Chronolog\Scriber\SysLogScriber;
use PHPUnit\Framework\TestCase;

/**
 * SysLogScriberTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 14:26:15
 */
class SysLogScriberTest extends TestCase
{
    public function testConstructor()
    {
        $scriber = new SysLogScriber();
        $this->assertInstanceOf('Chronolog\Scriber\SysLogScriber', $scriber);

        $scriber = new SysLogScriber(['severity' => Severity::Debug]);
        $this->assertInstanceOf('Chronolog\Scriber\SysLogScriber', $scriber);
    }

    public function testConstructorViaFactory()
    {
        $scriber = SysLogScriber::createInstance('syslog');
        $this->assertInstanceOf('Chronolog\Scriber\SysLogScriber', $scriber);

        $scriber = SysLogScriber::createInstance('syslog', severity: Severity::Info);
        $this->assertInstanceOf('Chronolog\Scriber\SysLogScriber', $scriber);
    }

    public function testIsAllowedSeverity()
    {
        $scriber1 = new SysLogScriber(['severity' => Severity::Error]);
        $scriber2 = new SysLogScriber(['severity' => [Severity::Debug, Severity::Info]]);

        $record = new LogEntity(new DateTimeStatement(), Severity::Error, "Simple message", "test");

        $this->assertTrue($scriber1->isAllowedSeverity($record));
        $this->assertFalse($scriber2->isAllowedSeverity($record));
    }

    public function testWrite()
    {
        $scriber = new SysLogScriber([
            'prefix' => 'syslog',
            'facility' => LOG_USER,
            'flags' => LOG_CONS,
            'severity' => Severity::Info,
            'renderer' => new StringRenderer([
                'pattern' => "%severity_name%: %message% %assets%",
                'allow_multiline' => false,
                'include_traces' => false,
                'base_path' => dirname(__DIR__, 2) . '/src'
            ])
        ]);
        $record = new LogEntity(new DateTimeStatement(), Severity::Info, "Simple info message", "test");
        $this->assertTrue($scriber->handle($record));
    }
}
/** End of SysLogScriberTest **/
