<?php
/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Chronolog\Test;

use Chronolog\DateTimeStatement;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class DateTimeStatementTest extends TestCase
{
    public function testToString()
    {
        $dateTime = new DateTimeStatement();
        $this->assertEquals(date(DateTimeStatement::ATOM), $dateTime->__toString());
    }

    public function testGetFormat()
    {
        $dateTime = new DateTimeStatement();
        $this->assertEquals(DateTimeStatement::ATOM, $dateTime->getFormat());
    }

    public function testSetFormat()
    {
        $dateTime = new DateTimeStatement();
        $newFormat = 'Y-m-d H:i:s';
        $dateTime->setFormat($newFormat);
        $this->assertEquals($newFormat, $dateTime->getFormat());
    }

    public function testConstructWithFormatAndTimezone()
    {
        $format = 'Y-m-d H:i:s';
        $timezone = new DateTimeZone('UTC');
        $dateTime = new DateTimeStatement($format, $timezone);
        $this->assertEquals($format, $dateTime->getFormat());
        $this->assertEquals($timezone, $dateTime->getTimezone());
    }
}