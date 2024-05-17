<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chronolog;

use Chronolog\Extender\ExtenderInterface;
use Chronolog\Helper\ArrayHelper;
use Chronolog\Helper\StringHelper;
use Chronolog\Scriber\ScriberAbstract;
use Chronolog\Scriber\ScriberInterface;
use DateTimeZone;
use RuntimeException;

/**
 * Undocumented class
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @package @date 2024-05-04 15:05:34
 */
class LogBook extends AutoInitialized
{
    protected string $track = 'log';
    protected mixed $scribes = [];
    protected mixed $extenders = [];
    protected ?DateTimeZone $timezone = null;
    protected bool $enabled = true;

    /**
     * Retrieves an array of scribes.
     *
     * @return array An array of scribes.
     */
    public function getScribes(): mixed
    {
        return $this->scribes;
    }

    /**
     * Sets the scribes for the LogBook.
     *
     * @param array $scribes An array of scribes.
     * @return self Returns the LogBook instance.
     */
    public function setScribes(mixed $scribes): self
    {
        foreach ($scribes as $scribe) {
            if(!is_object($scribe)) {
                $scribe = AutoInitialized::turnInto($scribe);
            }

            if (!$scribe instanceof ScriberInterface) {
                throw new RuntimeException(sprintf('%s: failed setting scribes; invalid scribe instance "%s"', __METHOD__, StringHelper::className($scribe, true)));
            }

            $this->scribes[] = $scribe;
        }
        return $this;
    }

    /**
     * Retrieves the extenders associated with the LogBook.
     *
     * @return array An array of extenders.
     */
    public function getExtenders(): mixed
    {
        return $this->extenders;
    }

    /**
     * Sets the extenders for the LogBook.
     *
     * @param mixed $extenders The extenders to set.
     * @return self
     */
    public function setExtenders($extenders): self
    {
        foreach ($extenders as $extender) {
            if(!is_object($extender)) {
                $extender = AutoInitialized::turnInto($extender);
            }

            if (!$extender instanceof ExtenderInterface) {
                throw new RuntimeException(sprintf('%s: failed setting extenders; invalid extender instance "%s"', __METHOD__, StringHelper::className($extender, true)));
            }

            $this->extenders[] = $extender;
        }

        return $this;
    }

    /**
     * Returns the timezone of the log book.
     *
     * @return DateTimeZone The timezone of the log book.
     */
    public function getTimezone(): DateTimeZone
    {
        return $this->timezone ?? new DateTimeZone(date_default_timezone_get());
    }


    /**
     * Sets the timezone for the LogBook.
     *
     * @param DateTimeZone $timezone The timezone to set.
     * @return self Returns the updated LogBook instance.
     */
    public function setTimezone(DateTimeZone $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get the value of track
     */
    public function getTrack(): string
    {
        return $this->track;
    }

    /**
     * Set the value of track
     *
     * @return  self
     */
    public function setTrack($track): self
    {
        $this->track = $track;

        return $this;
    }


    protected function log(int|Severity $severity, string $message, mixed $assets = [], null|DateTimeStatement $datetime = null): bool
    {
        if (!$this->enabled) {
            return false;
        }
        
        if (is_int($severity)) {
            $severity = Severity::fromValue($severity);
        }

        $entity = new LogEntity($datetime ?? new DateTimeStatement(timezone: $this->getTimezone()), $severity, $message, $this->getTrack(), $assets);

        foreach ($this->getExtenders() as $extender) {
            /** @var ExtenderInterface $extender */
            $entity = $extender($entity);
        }

        if (!$entity->relevant) {
            return false;
        }

        $result = false;
        foreach ($this->getScribes() as $scriber) {
            /** @var ScriberAbstract $scriber */
            if ($scriber->isAllowedSeverity($entity)) {
                if ($result = $scriber->handle($entity)) {
                    break;
                }
            }
        }

        return $result;
    }

    public function emergency(string $message, mixed $assets = []): bool
    {
        return $this->log(Severity::Emergency, $message, $assets);
    }

    public function alert(string $message, mixed $assets = []): bool
    {
        return $this->log(Severity::Alert, $message, $assets);
    }

    public function critical(string $message, mixed $assets = []): bool
    {
        return $this->log(Severity::Critical, $message, $assets);
    }

    public function error(string $message, mixed $assets = []): bool
    {
        return $this->log(Severity::Error, $message, $assets);
    }

    public function warning(string $message, mixed $assets = []): bool
    {
        return $this->log(Severity::Warning, $message, $assets);
    }

    public function notice(string $message, mixed $assets = []): bool
    {
        return $this->log(Severity::Notice, $message, $assets);
    }

    public function info(string $message, mixed $assets = []): bool
    {
        return $this->log(Severity::Info, $message, $assets);
    }

    public function debug(string $message, mixed $assets = []): bool
    {
        return $this->log(Severity::Debug, $message, $assets);
    }

    /**
     * Get the value of enabled
     */ 
    public function getEnabled():bool
    {
        return $this->enabled;
    }

    /**
     * Set the value of enabled
     *
     * @return  self
     */ 
    public function setEnabled(bool $enabled):self
    {
        $this->enabled = $enabled;

        return $this;
    }
}
