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

use Chronolog\Severity;
use PHPUnit\Framework\TestCase;

class SeverityTest extends TestCase
{
    public function testEnumValues()
    {
        $this->assertEquals(0, Severity::Emergency->value);
        $this->assertEquals(1, Severity::Alert->value);
        $this->assertEquals(2, Severity::Critical->value);
        $this->assertEquals(3, Severity::Error->value);
        $this->assertEquals(4, Severity::Warning->value);
        $this->assertEquals(5, Severity::Notice->value);
        $this->assertEquals(6, Severity::Info->value);
        $this->assertEquals(7, Severity::Debug->value);
    }

    public function testGetName()
    {
        $this->assertEquals('EMERGENCY', Severity::Emergency->getName());
        $this->assertEquals('ALERT', Severity::Alert->getName());
        $this->assertEquals('CRITICAL', Severity::Critical->getName());
        $this->assertEquals('ERROR', Severity::Error->getName());
        $this->assertEquals('WARNING', Severity::Warning->getName());
        $this->assertEquals('NOTICE', Severity::Notice->getName());
        $this->assertEquals('INFO', Severity::Info->getName());
        $this->assertEquals('DEBUG', Severity::Debug->getName());
    }

    public function testFromName()
    {
        $this->assertEquals(Severity::Emergency, Severity::fromName('EMERGENCY'));
        $this->assertEquals(Severity::Alert, Severity::fromName('ALERT'));
        $this->assertEquals(Severity::Critical, Severity::fromName('CRITICAL'));
        $this->assertEquals(Severity::Error, Severity::fromName('ERROR'));
        $this->assertEquals(Severity::Warning, Severity::fromName('WARNING'));
        $this->assertEquals(Severity::Notice, Severity::fromName('NOTICE'));
        $this->assertEquals(Severity::Info, Severity::fromName('INFO'));
        $this->assertEquals(Severity::Debug, Severity::fromName('DEBUG'));
    }

    public function testFromValue()
    {
        $this->assertEquals(Severity::Emergency, Severity::fromValue(0));
        $this->assertEquals(Severity::Alert, Severity::fromValue(1));
        $this->assertEquals(Severity::Critical, Severity::fromValue(2));
        $this->assertEquals(Severity::Error, Severity::fromValue(3));
        $this->assertEquals(Severity::Warning, Severity::fromValue(4));
        $this->assertEquals(Severity::Notice, Severity::fromValue(5));
        $this->assertEquals(Severity::Info, Severity::fromValue(6));
        $this->assertEquals(Severity::Debug, Severity::fromValue(7));
    }
}