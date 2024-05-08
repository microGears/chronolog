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


/**
 * Class StringHelper
 * 
 */
class StringHelper
{
    public static function isSerialized($str)
    {
        return $str === 'b:0;' || @unserialize($str) !== false;
    }

    function isAscii($str)
    {
        return (bool)!preg_match('/[\x80-\xFF]/', $str);
    }

    public static function clearInvisibleChars(string $str, bool $url_encoded = true)
    {
        $non_displayables = [];

        // every control character except newline (dec 10)
        // carriage return (dec 13), and horizontal tab (dec 09)

        if ($url_encoded) {
            $non_displayables[] = '/%0[0-8bcef]/'; // url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/'; // url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-08, 11, 12, 14-31, 127

        do {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
            if (null === $str) {
                self::throwPcreError(preg_last_error());
            }
        } while ($count);

        return $str;
    }

    public static function clearNewlines(string $str, bool $line_breaks = false): string
    {
        if ($line_breaks === true) {
            if (0 === strpos($str, '{') || 0 === strpos($str, '[')) {
                $str = preg_replace('/(?<!\\\\)\\\\[rn]/', "\n", $str);
                if (null === $str) {
                    self::throwPcreError(preg_last_error());
                }
            }
            return $str;
        }

        return str_replace(["\r\n", "\r", "\n"], ' ', $str);
    }

    public static function throwPcreError(int $code): string
    {
        $msg = 'Unknown error';

        if (PHP_VERSION_ID >= 80000) {
            $msg = preg_last_error_msg();
        } else {
            $constants = (get_defined_constants(true))['pcre'];
            $constants = array_filter($constants, function ($key) {
                return substr($key, -6) == '_ERROR';
            }, ARRAY_FILTER_USE_KEY);

            $constants = array_flip($constants);
            $msg = $constants[$code] ?? 'Unknown error';
        }

        throw new \RuntimeException('Failed to run preg_replace: ' . $code . ' / ' . $msg);
    }
}

/* End of file StringHelper.php */
