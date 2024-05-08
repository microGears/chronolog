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
use Chronolog\Scriber\Renderer\StringRenderer;
use Chronolog\Severity;
use PHPUnit\Framework\TestCase;

/**
 * StringRendererTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 08.05.2024 12:51:01
 */
class StringRendererTest  extends TestCase
{
    public function testRender(){
        $render = new StringRenderer(['format' => 'Y/m/d']);
        $line = $render->render(
            new LogRecord(
                new DateTimeStatement('Ymd'),
                Severity::Debug,
                'message text',
                'test'
            )
        );
        echo $line;
        $this->assertEquals('['.date('Y/m/d').']: test DEBUG message text []'."\n", $line);
    }
}
/** End of StringRendererTest **/   