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

class JsonHelper
{
    const DEFAULT_JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR;

    public static function isJson($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    public static function encode($data, ?int $encodeFlags = null, bool $ignoreErrors = false): string
    {
        if (null === $encodeFlags) {
            $encodeFlags = self::DEFAULT_JSON_FLAGS;
        }

        if ($ignoreErrors) {
            $json = @json_encode($data, $encodeFlags);
            if (false === $json) {
                return 'null';
            }

            return $json;
        }

        $json = json_encode($data, $encodeFlags);
        if (false === $json) {
            self::throwEncodeError(json_last_error(), $data);
        }

        return $json;
    }

    private static function throwEncodeError(int $code, $data): never
    {
        $msg = match ($code) {
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Occurs with underflow or with the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            JSON_ERROR_RECURSION => 'Object or array include recursive references and cannot be encoded',
            JSON_ERROR_INF_OR_NAN => 'A value includes either NAN or INF',
            JSON_ERROR_UNSUPPORTED_TYPE => 'A value of an unsupported type was given',
            default => 'Unknown error',
        };

        throw new \RuntimeException('JSON encoding failed: ' . $msg . '. Encoding: ' . var_export($data, true));
    }
}

/* End of file Json.php */
