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
use Chronolog\LogEntity;
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
    public function testSetTags()
    {
        $tagExtender = new TagExtender();
        $tags = ['tag1', 'tag2', 'tag3'];
        $result = $tagExtender->setTags($tags);
        $this->assertInstanceOf(TagExtender::class, $result);
    }
    
    public function testGetTags()
    {
        $tagExtender = new TagExtender();
        $tags = ['tag1', 'tag2', 'tag3'];
        $tagExtender->setTags($tags);
        $this->assertEquals($tags, $tagExtender->getTags());
    }
    
    public function testInvoke()
    {
        $tagExtender = new TagExtender();
        $tags = ['tag1', 'tag2', 'tag3'];
        $tagExtender->setTags($tags);
        $logEntity = new LogEntity(new DateTimeStatement(), Severity::Info, "Test for __invoke", 'test');
        $result = $tagExtender($logEntity);
        $this->assertArrayHasKey('tags', $result->assets);
        $this->assertEquals($tags, $result->assets['tags']);
    }
   
}
/** End of TagExtenderTest **/
