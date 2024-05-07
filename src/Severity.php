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
 * Represents the log severity
 *
 * Support the logging levels described by RFC 5424 {@see https://datatracker.ietf.org/doc/html/rfc5424}
 * To get the severity name/value out of a Severity there are several options:
 * - Use ->getName() to get the standard name which is full uppercased (e.g. "DEBUG")
 * - Use ->name to get the enum case's name which is capitalized (e.g. "Debug")
 */
enum Severity: int
{
    /** Emergency: system is unusable */
    case Emergency = 0;
    /** Alert: action must be taken immediately */
    case Alert = 1;
    /** Critical: critical conditions */
    case Critical = 2;
    /** Error: error conditions */
    case Error = 3;
    /** Warning: warning conditions */
    case Warning = 4;
    /** Notice: normal but significant condition */
    case Notice = 5;
    /** Informational: informational messages */
    case Info = 6;
    /** Debug: debug-level messages */
    case Debug = 7;

    const NAMES = [
        self::Emergency => 'EMERGENCY',
        self::Alert => 'ALERT',
        self::Critical => 'CRITICAL',
        self::Error => 'ERROR',
        self::Warning => 'WARNING',
        self::Notice => 'NOTICE',
        self::Info => 'INFO',
        self::Debug => 'DEBUG',
    ];

    /**
     * Returns the standardized all-capitals name of the severity
     *
     * @return string
     */
    public function getName(): string
    {
        return match ($this) {
            self::Emergency => 'EMERGENCY',
            self::Alert => 'ALERT',
            self::Critical => 'CRITICAL',
            self::Error => 'ERROR',
            self::Warning => 'WARNING',
            self::Notice => 'NOTICE',
            self::Info => 'INFO',
            self::Debug => 'DEBUG',
        };
    }

    public static function fromName(string $name): self
    {
        return match (strtoupper($name)) {
            'EMERGENCY' => self::Emergency,
            'ALERT' => self::Alert,
            'CRITICAL' => self::Critical,
            'ERROR' => self::Error,
            'WARNING' => self::Warning,
            'NOTICE' => self::Notice,
            'INFO' => self::Info,
            'DEBUG' => self::Debug,
        };
    }

    public static function fromValue(int $value): self
    {
        return self::from($value);
    }
}
