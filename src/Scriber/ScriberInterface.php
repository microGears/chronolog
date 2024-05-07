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

/**
 * ScriberInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 10:58:08
 */
interface ScriberInterface
{
   public function handle(LogRecord $record): bool;
}
/** End of ScriberInterface **/
