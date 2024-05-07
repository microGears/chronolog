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
 * SeverityTrait
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 13:45:20
 */
trait SeverityTrait
{
    /** @var array|Severity */
    protected mixed $severity;

    public function isAllowedSeverity(LogRecord $record): bool
    {
        /** Inclusion strategy */
        if ($this->severity instanceof Severity) {
            return $this->severity->value >= $record->severity->value;
        }

        /** Selective strategy */
        if (is_array($this->severity) && count($this->severity) > 0) {
            foreach ($this->severity as $severity) {
                if (!$severity instanceof Severity) continue;

                /** @var Severity $severity */
                if ($severity->value == $record->severity->value) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the value of severity
     */
    public function getSeverity(): array|Severity
    {
        return $this->severity;
    }

    /**
     * Set the value of severity
     *
     * @return  self
     */
    public function setSeverity(array|Severity $severity): self
    {
        $this->severity = $severity;

        return $this;
    }
}
/** End of SeverityTrait **/
