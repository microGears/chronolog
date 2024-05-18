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

use Chronolog\Extender\ExtenderAbstract;
use Chronolog\LogEntity;

/**
 * VersionExtender
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 17.05.2024 12:22:00
 */
class VersionExtender extends ExtenderAbstract
{
    public function __invoke(LogEntity $entity): LogEntity
    {
        if ($ver = $this->getVersion()) {
            $entity->assets['ver'] = $ver;
        }

        return $entity;
    }

    private function getVersion(): mixed
    {
        $version = exec('git describe --tags --always 2>&1', $output, $return_var);
        if ($return_var !== 0) {
            return false;
        }

        if (preg_match('/(?<=\D)?\d[\d\.]*/', $version, $matches)) {
            $version = $matches[0];
        }

        return $version;
    }
}
/** End of VersionExtender **/
