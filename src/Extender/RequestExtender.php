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
use Chronolog\Helper\StringHelper;
use Chronolog\LogEntity;

/**
 * RequestExtender
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 17.05.2024 10:16:00
 */
class RequestExtender extends ExtenderAbstract
{
    public const REQUEST_CLI  = 8;
    public const REQUEST_HTTP = 16;
    public const REQUEST_AJAX = 32;

    protected static $user_agent_headers = [
        // The default User-Agent string.
        'HTTP_USER_AGENT',
        // Header can occur on devices using Opera Mini.
        'HTTP_X_OPERAMINI_PHONE_UA',
        // Vodafone specific header: http://www.seoprinciple.com/mobile-web-community-still-angry-at-vodafone/24/
        'HTTP_X_DEVICE_USER_AGENT',
        'HTTP_X_ORIGINAL_USER_AGENT',
        'HTTP_X_SKYFIRE_PHONE',
        'HTTP_X_BOLT_PHONE_UA',
        'HTTP_DEVICE_STOCK_UA',
        'HTTP_X_UCBROWSER_DEVICE_UA',
    ];

    public function __invoke(LogEntity $entity): LogEntity
    {
        $entity->assets['request'] = [
            'type'       => $this->getRequestType(),
            'method'     => $this->getMethod(),
            'uri'        => $this->getUri(),
            'user_agent' => $this->getUserAgent(),
            'user_ip'    => $this->getUserIP(),
        ];

        return $entity;
    }

    public function getMethod(): string
    {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }

    public function getRequestType(): int
    {
        $result = 0;
        if (php_sapi_name() === 'cli' || defined('STDIN')) {
            $result = self::REQUEST_CLI;
        } elseif (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = self::REQUEST_AJAX;
        } else {
            $result = self::REQUEST_HTTP;
        }

        return $result;
    }

    public function getUserAgent(): string
    {
        $result = '';
        foreach (self::$user_agent_headers as $header) {
            if (isset($_SERVER[$header]) && false === empty($_SERVER[$header])) {
                $result .= $_SERVER[$header] . ' ';
            }
        }

        $result = trim((empty($result) ? 'Undefined' : $result));
        $result = substr($result, 0, 512);

        return $result;
    }

    public function getUserIP(): string
    {
        $result = '0.0.0.0';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $result = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $result = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $result = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $result = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $result = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $result = $_SERVER['REMOTE_ADDR'];
        }

        return $result;
    }

    public function getUri(): string
    {
        // Is there a REQUEST_URI variable?
        if (isset($_SERVER['REQUEST_URI'])) {

            $uri = $_SERVER['REQUEST_URI'];
            if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
                $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
            } elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
                $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
            }

            // This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
            // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
            if (strncmp($uri, '?/', 2) === 0) {
                $uri = substr($uri, 2);
            }

            $parts  = preg_split('#\?#i', $uri, 2);
            $uri = $parts[0];

            if (isset($parts[1])) {
                $_SERVER['QUERY_STRING'] = $parts[1];
                parse_str($_SERVER['QUERY_STRING'], $_GET);
            } else {
                $_SERVER['QUERY_STRING'] = '';
                $_GET                    = [];
            }

            if ($uri == '/' || empty($uri)) {
                return '/';
            }

            $uri = parse_url($uri, PHP_URL_PATH);
            $uri = str_replace(['//', '../'], '/', trim($uri, '/'));
            
            return $this->getUriString($uri);
        }

        // Is there a PATH_INFO variable?
        // Note: some servers seem to have trouble with getenv() so we'll test it two ways
        $uri = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
        if (trim($uri, '/') != '' && $uri != "/") {
            return $this->getUriString($uri);
        }

        // No PATH_INFO?... What about QUERY_STRING?
        $uri = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
        if (trim($uri, '/') != '') {
            return $this->getUriString($uri);
        }

        // As a last ditch effort lets try using the $_GET array
        if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '') {
            return $this->getUriString(key($_GET));
        }

        return '/';
    }

    private function getUriString($str): string
    {
        $str = StringHelper::clearInvisibleChars('/' . ltrim($str, '/'), false);
        $str = ($str == '/') ? '/' : urldecode($str);
        return $str;
    }
}
/** End of RequestExtender **/
