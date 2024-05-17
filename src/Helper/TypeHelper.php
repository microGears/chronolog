<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chronolog\Helper;

use Chronolog\Utilits;

class TypeHelper
{
    const TYPE_ARRAY    = 'array';
    const TYPE_BOOL     = 'bool';
    const TYPE_CALLABLE = 'callable';
    const TYPE_FLOAT    = 'float';
    const TYPE_INT      = 'int';
    const TYPE_NULL     = 'null';
    const TYPE_NUMERIC  = 'numeric';
    const TYPE_OBJECT   = 'object';
    const TYPE_RESOURCE = 'resource';
    const TYPE_STRING   = 'string';

    const TYPES = [
        self::TYPE_ARRAY,
        self::TYPE_BOOL,
        self::TYPE_CALLABLE,
        self::TYPE_FLOAT,
        self::TYPE_INT,
        self::TYPE_NULL,
        self::TYPE_NUMERIC,
        self::TYPE_OBJECT,
        self::TYPE_RESOURCE,
        self::TYPE_STRING,
    ];

    /**
     * Get the type of a variable
     *
     * @param $var
     *
     * @return string
     */
    public static function getType($var)
    {
        foreach (self::TYPES as $type) {
            if (function_exists($func = "is_{$type}")) {
                if (call_user_func($func, $var) === true) {
                    return $type;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Checks if a variable of the specified type is
     *
     * @param mixed  $var
     * @param string $type
     *
     * @return bool
     */
    public static function isType($var, $type = self::TYPE_NULL)
    {
        if (function_exists($func = "is_{$type}")) {
            return call_user_func($func, $var);
        }

        return false;
    }

    function A(){
        $arr = [];
        $branches = shell_exec('git branch -v --no-abbrev');
        if (is_string($branches) && 1 === preg_match('{^\* (.+?)\s+([a-f0-9]{40})(?:\s|$)}m', $branches, $matches)) {
            $arr = [
                'branch' => $matches[1],
                'commit' => $matches[2],
            ];
        }

        var_export($branches);
        var_export($arr);
    }

    function B(){
        $version = exec('git describe --tags --always 2>&1', $output, $return_var);

        if ($return_var !== 0) {
            throw new \RuntimeException("Не удалось получить версию проекта: " . implode("\n", $output));
        }

        $version = 'ver0.0.2-12-ga5aa41e';
        // $version = '1.8.5.19';
        if(preg_match('/(?<=\D)?\d[\d\.]*/', $version, $matches)){
            echo $matches[0];
        }

        echo $version;
    }

    function C(){
        $branch = exec('git rev-parse --abbrev-ref HEAD 2>&1', $output, $return_var);
    
        if ($return_var !== 0) {
            throw new \RuntimeException("Не удалось получить имя ветки: " . implode("\n", $output));
        }
    
        echo $branch;
    }
}

/* End of file TypeHelper.php */
