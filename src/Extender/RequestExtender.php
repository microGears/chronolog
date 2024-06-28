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
use Chronolog\Helper\ArrayHelper;
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

    public const REQUEST_TYPES = [
        self::REQUEST_CLI  => 'CLI',
        self::REQUEST_HTTP => 'HTTP',
        self::REQUEST_AJAX => 'AJAX',
    ];
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

    /**
     * The array of properties to exclude from the request extender.
     *
     * @var array
     */
    protected array $exclude = [];

    /**
     * Invokes the RequestExtender.
     *
     * @param LogEntity $entity The LogEntity object.
     * @return LogEntity The modified LogEntity object.
     */
    public function __invoke(LogEntity $entity): LogEntity
    {
        $result = [
            'type'       => $this->getRequestType(),
            'type_name'  => $this->getRequestTypeName(),
            'method'     => $this->getMethod(),
            'uri'        => $this->getUri(),
            'user_agent' => $this->getUserAgent(),
            'user_ip'    => $this->getUserIP(),
        ];

        if (count($this->exclude) > 0) {
            $result = ArrayHelper::filter($this->exclude, $result);
        }

        $entity->assets['request'] = $result;

        return $entity;
    }

    /**
     * Returns the HTTP method of the request.
     *
     * @return string The HTTP method.
     */
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

    /**
     * Returns the type name of the request.
     *
     * @return string The type name of the request.
     */
    public function getRequestTypeName(): string
    {
        return self::REQUEST_TYPES[$this->getRequestType()];
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

    /**
     * Retrieves the IP address of the user making the request.
     *
     * @return string The IP address of the user.
     */
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

    /**
     * Returns the URI of the request.
     *
     * @return string The URI of the request.
     */
    public function getUri(): string
    {
        // Is the request coming from the command line?
        if (php_sapi_name() == 'cli' or defined('STDIN')) {
            $uri = array_slice($_SERVER['argv'], 1);
            $uri = $uri ? '/' . implode('/', $uri) : '';

            if (strncmp($uri, '?/', 2) === 0) {
                $uri = substr($uri, 2);
            }
            $parts = preg_split('#\?#i', $uri, 2);
            $uri   = $parts[0];
            if (isset($parts[1])) {
                parse_str($parts[1], $_GET);
            }

            return $this->getUriString($uri);
        }

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

    /**
     * Returns the URI string.
     *
     * @param string $str The input string.
     * @return string The URI string.
     */
    private function getUriString($str): string
    {
        $str = StringHelper::clearInvisibleChars('/' . ltrim($str, '/'), false);
        $str = ($str == '/') ? '/' : urldecode($str);
        return $str;
    }
}
/** End of RequestExtender **/
