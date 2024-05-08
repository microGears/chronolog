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
use Chronolog\LogRecord;
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
    public function testConstructor(){
        $scriber = new SysLogScriber();
        $this->assertInstanceOf('Chronolog\Scriber\SysLogScriber', $scriber);

        $scriber = new SysLogScriber(['severity' => Severity::Debug]);
        $this->assertInstanceOf('Chronolog\Scriber\SysLogScriber', $scriber);
    }

    public function testIsAllowedSeverity()
    {
        $scriber1 = new SysLogScriber(['severity' => Severity::Error]);
        $scriber2 = new SysLogScriber(['severity' => [Severity::Debug, Severity::Info]]);

        $record = new LogRecord(new DateTimeStatement(), Severity::Error, "Simple message", "test");
        
        $this->assertTrue($scriber1->isAllowedSeverity($record));
        $this->assertFalse($scriber2->isAllowedSeverity($record));
    }
}
/** End of SysLogScriberTest **/
