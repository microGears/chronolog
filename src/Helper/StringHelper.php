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

class StringHelper
{
    /**
     * Checks if the given data is serializable.
     *
     * @param mixed $data The data to check.
     * @return bool Returns true if the data is serializable, false otherwise.
     */
    public static function isSerializable(mixed $data): bool
    {
        return @unserialize(serialize($data), ['allowed_classes' => true]) !== false;
    }

    /**
     * Checks if a string contains only ASCII characters.
     *
     * @param string $str The string to check.
     * @return bool Returns true if the string contains only ASCII characters, false otherwise.
     */
    public static function isAscii(string $str): bool
    {
        return (bool)!preg_match('/[\x80-\xFF]/', $str);
    }

    /**
     * Removes invisible characters from a string.
     *
     * @param string $str The input string to be processed.
     * @param bool $url_encoded Whether the input string is URL encoded. Default is true.
     * @return string The processed string with invisible characters removed.
     */
    public static function clearInvisibleChars(string $str, bool $url_encoded = true): string
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
                self::throwPregError(preg_last_error());
            }
        } while ($count);

        return $str;
    }

    /**
     * Removes carriage return and line feed characters from a string.
     *
     * @param string $str The input string to be processed.
     * @return string The processed string with carriage return and line feed characters removed.
     */
    public static function clearCRLF(string $str): string
    {
        return str_replace(["\r\n", "\r", "\n"], ' ', $str);
    }

    /**
     * Returns the class name of an object or a class.
     *
     * @param mixed $class The object or class to get the class name from.
     * @param bool $basename Whether to return only the base name of the class.
     * @return string The class name.
     */
    public static function className(mixed $class, bool $basename = false): string
    {
        $parts = explode('\\', is_object($class) ? get_class($class) : (string)$class);
        if ($basename) {
            $className = array_pop($parts);
        } else
            $className = implode('\\', $parts);


        return $className;
    }

    /**
     * Normalizes a given name by removing any special characters or spaces.
     *
     * @param string $name The name to be normalized.
     * @return string The normalized name.
     */
    public static function normalizeName(string $name):string
    {
        return strtolower(strtr($name, ['-' => '', '_' => '', ' ' => '', '\\' => '', '/' => '']));
    }

    public static function limitLength(string $string, int $length, string $replacement = '...'): string
    {
        if (mb_strlen($string) > $length) {
            $string = mb_substr($string, 0, $length - mb_strlen($replacement)) . $replacement;
        }

        return $string;
    }
    
    /**
     * Throws an exception for a given preg error code.
     *
     * @param int $code The preg error code.
     * @return never This function never returns a value.
     */
    public static function throwPregError(int $code): never
    {
        $message = 'Unknown error';

        if (PHP_VERSION_ID >= 80000) {
            $message = preg_last_error_msg();
        } else {
            $constants = (get_defined_constants(true))['pcre'];
            $constants = array_filter($constants, function ($key) {
                return substr($key, -6) == '_ERROR';
            }, ARRAY_FILTER_USE_KEY);
            $constants = array_flip($constants);

            if (isset($constants[$code])) {
                $message = $constants[$code];
            }
        }

        throw new \RuntimeException('Replacement failed: ' . $message);
    }
}

/* End of file StringHelper.php */
