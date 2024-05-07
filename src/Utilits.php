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
  * Utilits
  *
  * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
  * @datetime 07.05.2024 09:44:11
  */
 final class Utilits
 {
    public static function className($class, $normalize = false) {
        $parts = explode('\\', is_object($class) ? get_class($class) : $class);

        $className = array_pop($parts);
        if ($normalize == true) {
            $className = self::normalizeName($className);
        }

        return $className;
    }

    public static function normalizeName($name) {
        return strtolower(strtr($name, ['-' => '', '_' => '', ' ' => '', '\\' => '', '/' => '']));
    }

        /**
     * Returns the trailing name component of a path.
     * Note: this method is not aware of the actual filesystem, or path components such as "..".
     *
     * @param string $path   A path string.
     * @param string $suffix If the name component ends in suffix this will also be cut off.
     * @return string the trailing name component of the given path.
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function basename($path, $suffix = '') {
        if (($len = mb_strlen($suffix)) > 0 && mb_substr($path, -$len) == $suffix) {
            $path = mb_substr($path, 0, -$len);
        }
        $path = rtrim(str_replace('\\', '/', $path), '/\\');
        if (($pos = mb_strrpos($path, '/')) !== false) {
            return mb_substr($path, $pos + 1);
        }

        return $path;
    }

    public static function clearInvisibleChars($str, $url_encoded = true) {
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
        } while ($count);

        return $str;
    }
 }
 /** End of Utilits **/