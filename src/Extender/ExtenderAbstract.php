<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



namespace Chronolog\Extender;

use Chronolog\AutoInitialized;
use Chronolog\Extender\ExtenderInterface;
use Chronolog\LogEntity;

/**
 * ExtenderAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 10:00:23
 */
abstract class ExtenderAbstract extends AutoInitialized implements ExtenderInterface
{
    abstract public function __invoke(LogEntity $record): LogEntity;
}
/** End of ExtenderAbstract **/
