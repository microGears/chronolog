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

use DateTimeZone;

/**
 * DateTimeStatement
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 06.05.2024 17:36:20
 */
class DateTimeStatement extends \DateTimeImmutable
{
    protected string $format;

    public function __construct(string $format = self::ATOM, ?DateTimeZone $timezone = null)
    {
        $this->format = $format;
        parent::__construct('now', $timezone);
    }

    public function __toString(): string
    {
        return $this->format($this->format);
    }

    /**
     * Get the value of format
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Set the value of format
     *
     * @return  self
     */
    public function setFormat(string $value)
    {
        $this->format = $value;

        return $this;
    }
}
/** End of DateTimeStatement **/
