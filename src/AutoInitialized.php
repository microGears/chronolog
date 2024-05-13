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

use Chronolog\Helper\StringHelper;
use RuntimeException;

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

    public static function createInstance(array $arr = []): mixed
    {
        $result = null;
        if (is_array($arr)) {
            $invokable = isset($arr['class']) ?? null;
            if (!class_exists((string)$invokable)) {
                throw new RuntimeException(sprintf('%s: failed retrieving "%s" via invokable class "%s"; class does not exist', __METHOD__, StringHelper::className($invokable, true), $invokable));
            }

            $config = isset($arr['config']) ?? array_diff_key($arr, array_fill_keys(['class', 'config'], 'empty'));
            if (count($config) > 0) {
                /** This will have an effect if the class of $invokable is a descendant of Chronolog\AutoInitialized */
                $result = new $invokable($config);
            } else
                $result = new $invokable();
        }
        return $result;
    }
}
/** End of AutoInitialized **/
