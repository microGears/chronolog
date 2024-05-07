<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Chronolog\Scriber;

use Chronolog\LogRecord;
use Chronolog\Scriber\ScriberAbstract;

/**
 * SysLogScriber
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 14:23:38
 */
class SysLogScriber extends ScriberAbstract
{
    public function handle(LogRecord $record): bool
    {
        return true;
    }
}
/** End of SysLogScriber **/
