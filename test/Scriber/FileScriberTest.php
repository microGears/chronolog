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
use Chronolog\Scriber\FileScriber;
use Chronolog\Scriber\Renderer\StringRenderer;
use Chronolog\Severity;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

/**
 * FileScriberTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 15.05.2024 09:55:00
 */
class FileScriberTest extends TestCase
{
    public function testConstructor()
    {
        $scriber = new FileScriber();
        $this->assertInstanceOf('Chronolog\Scriber\FileScriber', $scriber);

        $scriber = new FileScriber(['severity' => Severity::Debug]);
        $this->assertInstanceOf('Chronolog\Scriber\FileScriber', $scriber);
    }

    public function testConstructorViaFactory()
    {
        $scriber = FileScriber::createInstance(path: dirname(__DIR__, 2) . '/runtime/logs/');
        $this->assertInstanceOf('Chronolog\Scriber\FileScriber', $scriber);

        $scriber = FileScriber::createInstance(path: dirname(__DIR__, 2) . '/runtime/logs/', severity: Severity::Info);
        $this->assertInstanceOf('Chronolog\Scriber\FileScriber', $scriber);
    }

    public function testIsAllowedSeverity()
    {
        $scriber1 = new FileScriber(['severity' => Severity::Error]);
        $scriber2 = new FileScriber(['severity' => [Severity::Debug, Severity::Info]]);

        $record = new LogEntity(new DateTimeStatement(), Severity::Error, "Simple message", "test");

        $this->assertTrue($scriber1->isAllowedSeverity($record));
        $this->assertFalse($scriber2->isAllowedSeverity($record));
    }

    public function testWrite()
    {
        $scriber = new FileScriber([
            'severity' => Severity::Info,
            'renderer' => new StringRenderer([
                'pattern' => "[%datetime%]: %track%~%severity_name% %message% %assets%",
                'format' => 'Y-m-d\TH:i:s.vP',
                'allow_multiline' => false,
                'include_traces' => false,
                'base_path' => dirname(__DIR__, 2) . '/src'
            ]),
            'path' => dirname(__DIR__, 2) . '/runtime/logs/',
            'basename' => basename(__FILE__, '.php').'A',
            'size_threshold' => 1024,
            'max_files' => 10,
            'write_immediately' => true,
            'collaborative' => true
        ]);

        for($i = 0; $i < 100; $i++) {
            $record = new LogEntity(new DateTimeStatement(), Severity::Info, "Simple info message - {$i}", "test");
            $scriber->handle($record);
        }

        $this->assertTrue($scriber->getDataSize() == 0);
    }

    public function testWriteDelayed()
    {
        $scriber = new FileScriber([
            'severity' => Severity::Info,
            'renderer' => new StringRenderer([
                'pattern' => "[%datetime%]: %track%~%severity_name% %message% %assets%",
                'format' => 'Y-m-d\TH:i:s.vP',
                'allow_multiline' => false,
                'include_traces' => false,
                'base_path' => dirname(__DIR__, 2) . '/src'
            ]),
            'path' => dirname(__DIR__, 2) . '/runtime/logs/',
            'basename' => basename(__FILE__, '.php').'B',
            'size_threshold' => 1024,
            'max_files' => 10,
            'write_immediately' => false,
            'collaborative' => true
        ]);

        for($i = 0; $i < 100; $i++) {
            $record = new LogEntity(new DateTimeStatement(), Severity::Info, "Simple info message - {$i}", "test");
            $scriber->handle($record);
        }

        // simulation of delay - uncomment if necessary
        // sleep(5);

        $this->assertTrue($scriber->getDataSize() > 0);
    }
}
/** End of FileScriberTest **/
