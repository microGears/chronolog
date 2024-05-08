<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Chronolog;

/**
 * AutoInitialized
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 11:20:55
 */
class AutoInitialized
{
    use InitTrait;
    public function __construct(array $config = [])
    {
        $this->initialize($config);
    }
}
/** End of AutoInitialized **/
