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

use Avant\Base\Constants;
use finfo;

/**
 * Class FileHelper
 * @subpackage Avant\Helpers
 */
class FileHelper
{
    // File and Directory Modes
    const FILE_READ_MODE  = 0644;
    const FILE_WRITE_MODE = 0666;
    const DIR_READ_MODE   = 0755;
    const DIR_WRITE_MODE  = 0777;

    // File Stream Modes
    const FOPEN_READ                          = 'rb';
    const FOPEN_READ_WRITE                    = 'r+b';
    const FOPEN_WRITE_CREATE_DESTRUCTIVE      = 'wb';  // truncates existing file
    const FOPEN_READ_WRITE_CREATE_DESTRUCTIVE = 'w+b'; // truncates existing file
    const FOPEN_WRITE_CREATE                  = 'ab';
    const FOPEN_READ_WRITE_CREATE             = 'a+b';
    const FOPEN_WRITE_CREATE_STRICT           = 'xb';
    const FOPEN_READ_WRITE_CREATE_STRICT      = 'x+b';

    public static function deleteFile(string $filename)
    {
        $result = false;
        try {
            $result = unlink($filename);
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public static function deleteFiles(string $path, bool $del_dir = false, int $level = 0): bool
    {
        // Trim the trailing slash
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (!$current_dir = @opendir($path)) {
            return false;
        }

        while (false !== ($filename = @readdir($current_dir))) {
            if ($filename != "." and $filename != "..") {
                if (is_dir($path . DIRECTORY_SEPARATOR . $filename)) {
                    // Ignore empty folders
                    if (substr($filename, 0, 1) != '.') {
                        self::deleteFiles($path . DIRECTORY_SEPARATOR . $filename, $del_dir, $level + 1);
                    }
                } else {
                    self::deleteFile($path . DIRECTORY_SEPARATOR . $filename);
                }
            }
        }
        @closedir($current_dir);

        if ($del_dir && ($level > 0)) {
            return @rmdir($path);
        }

        return true;
    }

    public static function getDir(string $source_dir, bool $onlyTop = true, bool $recursion = false): mixed
    {
        static $filedata;
        $relative_path = $source_dir;

        if ($fp = @opendir($source_dir)) {
            // reset the array and make sure $source_dir has a trailing slash on the initial call
            if ($recursion === false) {
                $filedata   = [];
                $source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }

            // foreach (scandir($source_dir, 1) as $file) // In addition to being PHP5+, scandir() is simply not as fast
            while (false !== ($file = readdir($fp))) {
                if (@is_dir($source_dir . $file) and strncmp($file, '.', 1) !== 0 and $onlyTop === false) {
                    self::getDir($source_dir . $file . DIRECTORY_SEPARATOR, $onlyTop, true);
                } elseif (strncmp($file, '.', 1) !== 0) {
                    $filedata[$file]                  = self::getFileInfo($source_dir . $file);
                    $filedata[$file]['relative_path'] = $relative_path;
                }
            }

            return $filedata;
        } else {
            return false;
        }
    }

    public static function getFileExtension(string $file): string
    {
        $parts = explode('.', $file);

        return end($parts);
    }

    public static function getFileInfo(string $file, array $returned_values = ['name', 'path', 'size', 'type']): array|bool
    {
        $result = [];

        if (!file_exists($file)) {
            return false;
        }

        if (is_string($returned_values)) {
            $returned_values = explode(',', $returned_values);
        }

        foreach ($returned_values as $key) {
            switch ($key) {
                case 'name':
                    $result['name'] = basename($file);
                    break;
                case 'path':
                    $result['path'] = $file;
                    break;
                case 'size':
                    $result['size'] = filesize($file);
                    break;
                case 'date':
                    $result['date'] = filemtime($file);
                    break;
                case 'readable':
                    $result['readable'] = is_readable($file);
                    break;
                case 'writable':
                    // There are known problems using is_weritable on IIS.  It may not be reliable - consider fileperms()
                    $result['writable'] = is_writable($file);
                    break;
                case 'executable':
                    $result['executable'] = is_executable($file);
                    break;
                case 'fileperms':
                    $result['fileperms'] = fileperms($file);
                    break;
                case 'type':
                    $result['type'] = self::getFileType($file);
                    break;
            }
        }

        return $result;
    }

    public static function getFileType(string $filename): string
    {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME);
            if (is_resource($finfo)) // It is possible that a FALSE value is returned, if there is no magic MIME database file found on the system
            {
                /** @var finfo $finfo */
                $mime = @finfo_file($finfo, $filename);
                finfo_close($finfo);

                /* According to the comments section of the PHP manual page,
                 * it is possible that this function returns an empty string
                 * for some files (e.g. if they don't exist in the magic MIME database)
                 */
                $regexp = '/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/';
                if (is_string($mime) && preg_match($regexp, $mime, $matches)) {
                    return $matches[1];
                }
            }
        }

        // Fall back to the deprecated mime_content_type(), if available (still better than $_FILES[$field]['type'])
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($filename);
            if (strlen($mime) > 0) // It's possible that mime_content_type() returns FALSE or an empty string
            {
                return $mime;
            }
        }

        return 'application/octet-stream';
    }

    public static function getFiles(string $source_dir, bool $include_path = false, bool $recursion = false): array|bool
    {
        static $result = [];

        if ($fp = @opendir($source_dir)) {
            // reset the array and make sure $source_dir has a trailing slash on the initial call
            if ($recursion === false) {
                $result   = [];
                $source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }

            while (false !== ($file = readdir($fp))) {
                if (@is_dir($source_dir . $file) && strncmp($file, '.', 1) !== 0) {
                    self::getFiles($source_dir . $file . DIRECTORY_SEPARATOR, $include_path, true);
                } elseif (strncmp($file, '.', 1) !== 0) {
                    $result[] = ($include_path == true) ? $source_dir . $file : $file;
                }
            }

            return $result;
        } else {
            return false;
        }
    }

    public static function isReallyWritable(string $file): bool
    {
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR == '/' and @ini_get("safe_mode") == false) {
            return is_writable($file);
        }

        // For windows servers and safe_mode "on" installations we'll actually
        // write a file then read it.  Bah...
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand(1, 100) . mt_rand(1, 100));

            if (($fp = @fopen($file, self::FOPEN_WRITE_CREATE)) === false) {
                return false;
            }

            fclose($fp);
            @chmod($file, self::DIR_WRITE_MODE);
            @unlink($file);

            return true;
        } elseif (!is_file($file) or ($fp = @fopen($file, self::FOPEN_WRITE_CREATE)) === false) {
            return false;
        }

        fclose($fp);

        return true;
    }

    public static function octalPermissions($perms)
    {
        return substr(sprintf('%o', $perms), -3);
    }

    public static function prepFilename(string $filename):string
    {
        $path_prefix = (substr($filename, 0, 1) == '/') ? '/' : '';
        $parts = explode('/', trim($filename, '/'));
        $file = array_pop($parts);
        $file_normalize = $path_prefix . self::prepPath(implode('/', $parts), false) . (pathinfo($file, PATHINFO_EXTENSION) ? $file : $file . self::getFileExtension(__FILE__));

        return self::prepLocation($file_normalize);
    }

    public static function prepLocation(string $location): string
    {
        return str_replace(['\\', '//'], '/', $location);
    }

    public static function prepPath(string $path, bool $normalize = true):string
    {
        if (function_exists('realpath') && $normalize) {
            $path = realpath($path);
        }
        return rtrim(self::prepLocation($path), '/') . '/';
    }

    public static function readFile(string $file):bool|string
    {
        if (!file_exists($file)) {
            return false;
        }

        if (function_exists('file_get_contents')) {
            return file_get_contents($file);
        }

        if (!$fp = @fopen($file, self::FOPEN_READ)) {
            return false;
        }

        flock($fp, LOCK_SH);

        $data = '';
        if (filesize($file) > 0) {
            $data = &fread($fp, filesize($file));
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return $data;
    }

    public static function symbolicPermissions(mixed $perms):string
    {
        if (($perms & 0xC000) == 0xC000) {
            $symbolic = 's'; // Socket
        } elseif (($perms & 0xA000) == 0xA000) {
            $symbolic = 'l'; // Symbolic Link
        } elseif (($perms & 0x8000) == 0x8000) {
            $symbolic = '-'; // Regular
        } elseif (($perms & 0x6000) == 0x6000) {
            $symbolic = 'b'; // Block special
        } elseif (($perms & 0x4000) == 0x4000) {
            $symbolic = 'd'; // Directory
        } elseif (($perms & 0x2000) == 0x2000) {
            $symbolic = 'c'; // Character special
        } elseif (($perms & 0x1000) == 0x1000) {
            $symbolic = 'p'; // FIFO pipe
        } else {
            $symbolic = 'u'; // Unknown
        }

        // Owner
        $symbolic .= (($perms & 0x0100) ? 'r' : '-');
        $symbolic .= (($perms & 0x0080) ? 'w' : '-');
        $symbolic .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $symbolic .= (($perms & 0x0020) ? 'r' : '-');
        $symbolic .= (($perms & 0x0010) ? 'w' : '-');
        $symbolic .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));

        // World
        $symbolic .= (($perms & 0x0004) ? 'r' : '-');
        $symbolic .= (($perms & 0x0002) ? 'w' : '-');
        $symbolic .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));

        return $symbolic;
    }

    public static function writeFile(string $path, mixed $data, string $mode = self::FOPEN_WRITE_CREATE_DESTRUCTIVE)
    {

        if (!is_dir($dir = dirname($path))) {
            if (@mkdir($dir, self::DIR_WRITE_MODE, true) == false) {
                return false;
            }
        }

        if (!$fp = @fopen($path, $mode)) {
            return false;
        }

        flock($fp, LOCK_EX);
        fwrite($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }
}

/* End of file FileHelper.php */
