<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chronolog\Test\Scriber\Renderer;

use Chronolog\DateTimeStatement;
use Chronolog\LogRecord;
use Chronolog\Scriber\Renderer\BaseRenderer;
use Chronolog\Severity;
use PHPUnit\Framework\TestCase;

/**
 * BaseRendererTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 18:53:30
 */
class BaseRendererTest extends TestCase
{
    public function testRender()
    {
        $render = new BaseRenderer(['format' => 'Y/m/d']);
        $result = $render->render(
            new LogRecord(
                new DateTimeStatement('Y.m.d'),
                Severity::Debug,
                "simple message text",
                "test",
                [
                    "var_float" => 125.678,
                    "var_nan" => sqrt(-125.67),
                    "var_inf_1" => INF,
                    "var_inf_2" => -INF,
                    "var_string" => "string",
                    'var_res' => fopen('php://memory', 'rb'),
                    "var_null" => null,
                    "var_obj_1" => new TestClassOne(),
                    "var_obj_2" => new TestClassDecorator(),
                    "var_obj_3" => new TestClassStringableOne('ClassOne'),
                    "var_obj_4" => new TestClassStringableTwo('ClassTwo'),
                    "var_array_1" => [1, 2, 3, 4, 5, 6, 7],
                    "var_array_2" => ['key_1' => 1, 'key_2' => 2],
                    "var_date" => new \DateTime()
                ]
            )
        );
        // var_export($result);
        // return;
        $this->assertEquals([
            'datetime' => date('Y/m/d'),
            'severity' => Severity::Debug->value,
            'severity_name' => Severity::Debug->getName(),
            'message' => 'simple message text',
            'track' => 'test',
            'assets' =>
            [
                'var_float' => 125.678,
                'var_nan' => 'NaN',
                'var_inf_1' => 'INF',
                'var_inf_2' => '-INF',
                'var_string' => 'string',
                'var_res' => '[resource(stream)]',
                'var_null' => NULL,
                'var_obj_1' => ['Chronolog\\Test\\Scriber\\Renderer\\TestClassOne' => ['property' => 'value']],
                'var_obj_2' => ['Chronolog\\Test\\Scriber\\Renderer\\TestClassDecorator' => ['parent' => ['Chronolog\\Test\\Scriber\\Renderer\\TestClassOne' => ['property' => 'value']], 'items' => []]],
                'var_obj_3' => ['Chronolog\\Test\\Scriber\\Renderer\\TestClassStringableOne' => 'ClassOne'],
                'var_obj_4' => ['Chronolog\\Test\\Scriber\\Renderer\\TestClassStringableTwo' => 'ClassTwo'],
                'var_array_1' => [0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7],
                'var_array_2' => ['key_1' => 1, 'key_2' => 2],
                'var_date' => date('Y/m/d')
            ],
            'relevant' => true,
        ], $result);
    }
}
/** End of BaseRendererTest **/

class TestClassOne
{
    public $property = 'value';
}

class TestClassDecorator
{
    public TestClassOne $parent;
    public array $items = [];
    public function __construct()
    {
        $this->parent = new TestClassOne();
    }
}

class TestClassStringableOne implements \Stringable
{
    protected string $str;
    public function __construct(string $str)
    {
        $this->str = $str;
    }

    public function __toString(): string
    {
        return $this->str;
    }
}

class TestClassStringableTwo {
    protected string $str;
    public function __construct(string $str)
    {
        $this->str = $str;
    }
    public function __toString(){
        return $this->str;
    }
}
