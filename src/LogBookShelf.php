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
 * Represents a log book shelf.
 *
 * This class provides functionality to manage log books.
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 16.05.2024 12:17:00
 */
final class LogBookShelf
{
    private static mixed $shelf = [];

    /**
     * Stores a LogBook object in the shelf.
     *
     * @param LogBook $logbook The LogBook object to store.
     * @param bool $override (optional) Whether to override an existing LogBook with the same ID. Default is false.
     * @return void
     */
    public static function put(LogBook $logbook, bool $override = false): void
    {
        if (self::has($name = $logbook->getTrack()) && !$override) {
            return;
        }
        self::$shelf[$name] = $logbook;
    }

    /**
     * Retrieves a LogBook object based on the provided logbook name.
     *
     * @param string $logbook The name of the logbook to retrieve.
     * @return LogBook|null The LogBook object if found, or null if not found.
     */
    public static function get(string $logbook): ?LogBook
    {
        if (self::has($logbook)) {
            return self::$shelf[$logbook];
        }
        return null;
    }

    /**
     * Checks if a logbook exists in the shelf.
     *
     * @param mixed $logbook The logbook to check.
     * @return bool Returns true if the logbook exists, false otherwise.
     */
    public static function has(mixed $logbook): bool
    {
        if ($logbook instanceof LogBook) {
            return array_search($logbook, self::$shelf, true) !== false;
        }
        return array_key_exists($logbook, self::$shelf);
    }

    /**
     * Removes a logbook from the shelf.
     *
     * @param mixed $logbook The logbook to be removed.
     * @return void
     */
    public static function remove(mixed $logbook)
    {
        if ($logbook instanceof LogBook) {
            $key = array_search($logbook, self::$shelf, true);
            if ($key !== false) {
                unset(self::$shelf[$key]);
            }
        } else {
            if (array_key_exists($logbook, self::$shelf)) {
                unset(self::$shelf[$logbook]);
            }
        }
    }

    /**
     * Flushes the log book shelf.
     *
     * This method is responsible for clearing the log book shelf and removing all stored logs.
     * After calling this method, the log book shelf will be empty.
     *
     * @return void
     */
    public static function flush(): void
    {
        self::$shelf = [];
    }
}
/** End of LogBookShelf **/
