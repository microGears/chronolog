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

use Chronolog\LogEntity;

/**
 * ScriberInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 10:58:08
 */
interface ScriberInterface
{
   /**
    * Handles a log entity.
    *
    * @param LogEntity $entity The log entity to handle.
    * @return bool Returns true if the log object was processed successfully and needs to abort next processing, false otherwise.
    */
   public function handle(LogEntity $entity): bool;
}
/** End of ScriberInterface **/
