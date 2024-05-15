<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Chronolog\Scriber;

use Chronolog\LogEntity;
use Chronolog\Scriber\Renderer\StringRenderer;
use Chronolog\Scriber\Renderer\RendererInterface;
use Chronolog\Scriber\ScriberAbstract;
use Chronolog\Severity;
use RuntimeException;

/**
 * SyslogScriber
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 14:23:38
 */
class SyslogScriber extends ScriberAbstract
{
    const FACILITIES = [
        LOG_AUTH,
        LOG_AUTHPRIV,
        LOG_CRON,
        LOG_DAEMON,
        LOG_KERN,

        // Not available on Windows
        128, // LOG_LOCAL0,
        136, // LOG_LOCAL1,
        144, // LOG_LOCAL2,
        152, // LOG_LOCAL3,
        160, // LOG_LOCAL4,
        168, // LOG_LOCAL5,
        176, // LOG_LOCAL6,
        184, // LOG_LOCAL7,

        LOG_LPR,
        LOG_MAIL,
        LOG_NEWS,
        LOG_SYSLOG,
        LOG_USER,
        LOG_UUCP,
    ];

    const FLAGS = [
        LOG_CONS,
        LOG_NDELAY,
        LOG_ODELAY,
        LOG_PERROR,
        LOG_PID
    ];

    /**
     * The facility used for logging to syslog.
     *
     * @var int $facility The facility value. Default is LOG_USER.
     */
    protected int $facility = LOG_USER;
    /**
     * The flags used for the SyslogScriber.
     *
     * @var int $flags The flags for the SyslogScriber.
     */
    protected int $flags = LOG_PID;
    /**
     * @var string|null $prefix The prefix for the syslog messages.
     */
    protected ?string $prefix = null;

    /**
     * Handles a log entity.
     *
     * @param LogEntity $entity The log entity to handle.
     * @return bool Returns true if the log entity was handled successfully, false otherwise.
     */
    public function handle(LogEntity $entity): bool
    {
        if($this->isAllowedSeverity($entity) === false) {
            return false;
        }

        openlog($this->prefix, $this->flags, $this->facility);
        syslog($entity->severity->value, $this->getRenderer()->render($entity));

        if ($this->getCollaborative()) {
            return false;
        }

        return true;
    }

    public function getDefaultRenderer(): RendererInterface
    {
        return new StringRenderer();
    }

    /**
     * Get the value of facility
     */
    public function getFacility(): int
    {
        return $this->facility;
    }

    /**
     * Set the value of facility
     *
     * @return  self
     */
    public function setFacility(int $facility): self
    {
        if (!in_array($facility, self::FACILITIES))
            throw new RuntimeException(sprintf('%s: failed setting facility; invalid value "%d" given', __METHOD__, $facility));
        $this->facility = $facility;

        return $this;
    }

    /**
     * Get the value of flags
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * Set the value of flags
     *
     * @return  self
     */
    public function setFlags(int $flags): self
    {
        $valid_flags = array_reduce(self::FLAGS, function ($valid, $flag) use ($flags) {
            return $valid | ($flags & $flag);
        }, 0);

        if ($valid_flags !== $flags)
            throw new RuntimeException(sprintf('%s: failed setting flags; invalid value "%d" given', __METHOD__, $flags));

        $this->flags = $flags;

        return $this;
    }

    /**
     * Creates an instance of SyslogScriber.
     *
     * @param string $prefix The prefix to prepend to log messages.
     * @param int $facility The syslog facility to use (default: LOG_USER).
     * @param int $flags The flags to use (default: LOG_PID).
     * @param Severity|array $severity The severity level(s) to log (default: Severity::Debug).
     * @return self The created instance of SyslogScriber.
     */
    public static function createInstance(string $prefix, int $facility = LOG_USER, int $flags = LOG_PID, Severity|array $severity = Severity::Debug): self
    {
        return new SysLogScriber([
            'prefix' => $prefix,
            'facility' => $facility,
            'flags' => $flags,
            'severity' => $severity,
            'renderer' => new StringRenderer([
                'pattern' => "%severity_name%: %message% %assets%",
                'allow_multiline' => false,
                'include_traces' => false,
            ])
        ]);
    }
}
/** End of SyslogScriber **/
