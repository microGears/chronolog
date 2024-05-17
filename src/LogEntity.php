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

use ArrayAccess;

/**
 * LogRecord
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 06.05.2024 19:02:15
 */
class LogEntity implements ArrayAccess
{

    public function __construct(
        public readonly DateTimeStatement $datetime,
        public readonly Severity $severity,
        public readonly string $message,
        public readonly string $track, // belonging to the owner
        public mixed $assets = [], // non persistent properties
        public bool $relevant = true, // relevance of the entry        
    ) {
    }


    /**
     * Assigns a value to the specified offset
     *
     * @param  mixed $offset 
     * @param  mixed $value  
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->{$offset} = $value;
    }

    /**
     * Whether or not an offset exists
     *
     * @param  mixed   $offset 
     * @return boolean
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->{$offset});
    }

    /**
     * Unsets an offset
     *
     * @param  mixed $offset 
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        // do nothing
    }

    /**
     * Returns the value at specified offset
     *
     * @param  mixed $offset 
     * @return mixed
     */
    public function &offsetGet(mixed $offset): mixed
    {
        if ($this->offsetExists($offset)) {
            return $this->{$offset};
        }
        return null;
    }


    public function toArray()
    {
        return [
            'datetime' => $this->datetime,
            'severity' => $this->severity->value,
            'severity_name' => $this->severity->getName(),
            'message' => $this->message,
            'track' => $this->track,
            'assets' => $this->assets,
            'relevant' => $this->relevant
        ];
    }

    /**
     * Create self-copy
     *
     * @return LogEntity
     */
    public function fork():LogEntity{
        return static::clone($this);
    }

    /**
     * Replacing the standard clone method, 
     * because class contains properties in read-only state
     *
     * @param  LogEntity $source 
     * @return LogEntity
     */
    public static function clone(LogEntity $source):LogEntity{
        // first way
        // return new LogRecord(
        //     clone $source->datetime,
        //     $source->severity,
        //     $source->message,
        //     $source->track,
        //     unserialize(serialize($source->assets)),
        //     $source->relevant            
        // );

        // second way
        return unserialize(serialize($source), ['allowed_classes' => true]);
    }
}
/** End of LogRecord **/
