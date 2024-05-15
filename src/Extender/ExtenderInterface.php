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

use Chronolog\LogEntity;

/**
 * ExtenderInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 09:09:07
 */
interface ExtenderInterface
{
    public function __invoke(LogEntity $entity):LogEntity;
}
/** End of ExtenderInterface **/
