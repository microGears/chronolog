<?php

namespace Chronolog\Test\Extender;

use Chronolog\Extender\VersionExtender;
use Chronolog\LogEntity;
use Chronolog\DateTimeStatement;
use Chronolog\Severity;
use PHPUnit\Framework\TestCase;

class VersionExtenderTest extends TestCase
{
    public function testInvoke()
    {
        $extender = new VersionExtender();
        $entity = new LogEntity(new DateTimeStatement(), Severity::Info, "Test for __invoke", 'test');

        $result = $extender($entity);
        $this->assertInstanceOf(LogEntity::class, $result);
        $this->assertArrayHasKey('ver', $result->assets);
    }
}