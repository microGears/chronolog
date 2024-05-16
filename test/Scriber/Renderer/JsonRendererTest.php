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
 use Chronolog\LogEntity;
use Chronolog\Scriber\Renderer\JsonRenderer;
use Chronolog\Scriber\Renderer\StringRenderer;
 use Chronolog\Severity;
 use Exception;
 use PHPUnit\Framework\TestCase;
 use RuntimeException;
 use TypeError;

/**
 * JsonRendererTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 16.05.2024 14:07:00
 */
class JsonRendererTest extends TestCase
{
    public function testContrustor()
    {
        $renderer = new JsonRenderer();
        $this->assertInstanceOf('Chronolog\Scriber\Renderer\JsonRenderer', $renderer);
    }

    public function testRender(){
        $renderer = new JsonRenderer(['format' => 'Y-m-d H:i:s']);
        $logEntity = new LogEntity(
            new DateTimeStatement('Ymd'),
            Severity::Debug,
            'message text',
            'test',
            ['foo' => 'bar']
        );
        $output = $renderer->render($logEntity);
        $this->assertJson($output);
    }

    public function testRenderWithPrettyPrint(){
        $renderer = new JsonRenderer(['format' => 'Y-m-d H:i:s', 'flags' => JSON_PRETTY_PRINT]);
        $logEntity = new LogEntity(
            new DateTimeStatement('Ymd'),
            Severity::Debug,
            'message text',
            'test'
        );
        $output = $renderer->render($logEntity);
        $this->assertJson($output);
    }

    public function testRenderException(){
        $renderer = new JsonRenderer([
            'format' => 'Y-m-d H:i:s', 
            'allow_fullnamespace' => false,
            'include_traces' => false,
            'base_path' => dirname(__DIR__, 3) . '/src'
        ]);

        $logEntity = new LogEntity(
            new DateTimeStatement('Ymd'),
            Severity::Debug,
            'message text',
            'test',
            ['exception' => new Exception('exception message',123, new RuntimeException('runtime message', 321))]
        );
        
        $output = $renderer->render($logEntity);
        $this->assertJson($output);
    }
}
/** End of JsonRendererTest **/