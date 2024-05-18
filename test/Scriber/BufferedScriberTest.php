<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chronolog\DateTimeStatement;
use Chronolog\LogEntity;
use Chronolog\Scriber\BufferedScriber;
use Chronolog\Scriber\ErrorLogScriber;
use Chronolog\Scriber\FileScriber;
use Chronolog\Scriber\Renderer\StringRenderer;
use Chronolog\Severity;
use PHPUnit\Framework\TestCase;

class BufferedScriberTest extends TestCase
{
    public function testConstruct()
    {
        $scriber = new BufferedScriber();
        $this->assertInstanceOf(BufferedScriber::class, $scriber);
    }

    public function testConstructorViaFactory()
    {
        $scriber = ErrorLogScriber::createInstance();
        $wrapper = BufferedScriber::createInstance($scriber);
        $this->assertInstanceOf('Chronolog\Scriber\BufferedScriber', $wrapper);

        $wrapper = BufferedScriber::createInstance($scriber);
        $this->assertInstanceOf('Chronolog\Scriber\BufferedScriber', $wrapper);        
    }

    public function testHandle()
    {
        // FileScriber is used as an example, but this is inefficient because 
        // FileScriber has its own buffering mechanism.
        $wrapper = new BufferedScriber([
            'severity' => Severity::Debug,
            'scriber' => new FileScriber([
                'severity' => Severity::Info,
                'renderer' => new StringRenderer([
                    'pattern' => "[%datetime%]: %track%~%severity_name% %message% %assets%",
                    'format' => 'Y-m-d\TH:i:s.vP',
                    'base_path' => dirname(__DIR__, 2) . '/src'
                ]),
                'path' => dirname(__DIR__, 2) . '/runtime/logs/',
                'basename' => basename(__FILE__, '.php'),
                'size_threshold' => 1024,
                'max_files' => 10,
                'write_immediately' => true
            ]),
            'collaborative' => true,
            'max_items' => 5,
        ]);


        for ($i = 0; $i < 10; $i++) {
            $record = new LogEntity(new DateTimeStatement(), Severity::Info, "Simple info message - {$i}", "test");
            $wrapper->handle($record);
        }

        // There are 5 entries left in the buffer, they will be saved when the script ends
        $this->assertFalse($wrapper->getBufferSize() == 5);
    }

    public function testMaxItems()
    {
        $scriber = new BufferedScriber();
        $scriber->setMaxItems(10);
        $this->assertEquals(10, $scriber->getMaxItems());
    }

    public function testScriber()
    {
        $wrapper = new BufferedScriber();
        $scriber = $this->createMock(ErrorLogScriber::class);

        $wrapper->setScriber($scriber);
        $this->assertEquals($scriber, $wrapper->getScriber());

        $scriber = $this->createMock(BufferedScriber::class);
        $this->expectException(RuntimeException::class);
        $wrapper->setScriber($scriber);
    }

    public function testBufferSize()
    {
        $scriber = new BufferedScriber();
        $this->assertEquals(0, $scriber->getBufferSize());
    }
}
