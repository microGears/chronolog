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

use Chronolog\AutoInitialized;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AutoInitializedTest extends TestCase
{
    public function testConstruct()
    {
        $config = ['key' => 'value'];
        $result = new AutoInitialized($config);
        $this->assertInstanceOf(AutoInitialized::class, $result);
    }

    public function testTurnIntoWithString()
    {
        $className = AutoInitialized::class;
        $result = AutoInitialized::turnInto($className);
        $this->assertInstanceOf($className, $result);
    }

    public function testTurnIntoWithArray()
    {
        $className = MyAutoInitialized::class;
        $input = ['class' => $className, 'key1' => 1, 'key2' => 2];
        $result = AutoInitialized::turnInto($input);
        $this->assertInstanceOf($className, $result);
        $this->assertEquals(1, $result->key1);
        $this->assertEquals(2, $result->key2);

        $input = ['class' => $className, 'config' => ['key1' => 3, 'key2' => 4]];
        $result = AutoInitialized::turnInto($input);
        $this->assertInstanceOf($className, $result);
        $this->assertEquals(3, $result->key1);
        $this->assertEquals(4, $result->key2);

        $this->expectException(RuntimeException::class);
        AutoInitialized::turnInto([$className]);
    }

    public function testTurnIntoWithNonExistentClass()
    {
        $this->expectException(RuntimeException::class);
        AutoInitialized::turnInto('NonExistentClass');
    }

    public function testTurnIntoWithEmptyArray()
    {
        $this->expectException(RuntimeException::class);
        AutoInitialized::turnInto([]);
    }
}

class MyAutoInitialized extends AutoInitialized
{
    public $key1 = 0;
    public $key2 = 0;
}