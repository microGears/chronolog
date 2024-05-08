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

use Chronolog\DateTimeStatement;
use Chronolog\Extender\TagExtender;
use Chronolog\LogRecord;
use Chronolog\Severity;
use PHPUnit\Framework\TestCase;

/**
 * TagExtenderTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 10:12:49
 */
class TagExtenderTest extends TestCase
{
    public function testExtender()
    {
        $aug = new TagExtender(['tags' => ['tag1', 'tag2', 'tag3']]);
        $record = $aug(new LogRecord(new DateTimeStatement(), Severity::Info, "Simple test for TagExtender", 'test'));

        $this->assertArrayHasKey('tags', $record->assets);
        $this->assertIsArray($record->assets['tags']);
        $this->assertTrue(count($record->assets['tags']) > 0);
    }
}
/** End of TagExtenderTest **/
