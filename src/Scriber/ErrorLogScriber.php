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
use Chronolog\Scriber\Renderer\RendererInterface;
use Chronolog\Scriber\Renderer\StringRenderer;
use Chronolog\Severity;
use RuntimeException;

/**
 * ErrorLogScriber
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 13.05.2024 19:28:06
 */
class ErrorLogScriber extends ScriberAbstract
{
    const MSG_SYSTEM = 0;
    const MSG_EMAIL = 1;
    const MSG_FILE = 3;
    const MSG_SAPI = 4;
    const MSG_TYPES = [self::MSG_SYSTEM, self::MSG_EMAIL, self::MSG_FILE, self::MSG_SAPI];

    protected int $message_type = self::MSG_SYSTEM;
    protected ?string $destination = null;
    protected ?string $headers = null;

    /**
     * Handles a log entity.
     *
     * @param LogEntity $entity The log entity to handle.
     * @return bool Returns true if the log object was processed successfully and needs to abort next processing, false otherwise.
     */
    public function handle(LogEntity $entity): bool
    {
        return error_log(
            $this->getRenderer()->render($entity),
            $this->getMessageType(),
            $this->getDestination(),
            $this->getHeaders(),
        );
    }

    public function getDefaultRenderer(): RendererInterface
    {
        return new StringRenderer();
    }

    public function getMessageType(): int
    {
        return $this->message_type;
    }


    public function setMessageType(int $value): self
    {
        if (!in_array($value, self::MSG_TYPES))
            throw new RuntimeException(sprintf('%s: failed setting message type; invalid value of type - %d', __METHOD__, $value));

        $this->message_type = $value;

        return $this;
    }

    /**
     * Get the value of destination
     */
    public function getDestination(): ?string
    {
        return $this->destination;
    }

    /**
     * Set the value of destination
     *
     * @return  self
     */
    public function setDestination(string|null $destination): self
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * Get the value of headers
     */
    public function getHeaders(): ?string
    {
        return $this->headers;
    }

    /**
     * Set the value of headers
     *
     * @return  self
     */
    public function setHeaders(string|null $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public static function createInstance(Severity $severity = Severity::Debug, int $message_type = self::MSG_SYSTEM, ?string $destination = null, ?string $headers = null): self
    {
        return new ErrorLogScriber([
            'severity' => $severity,
            'renderer' => new StringRenderer([
                'pattern' => "%severity_name% %track%: %message% %assets%\n",
                'allow_multiline' => true,
                'include_traces' => true,
            ]),
            'message_type' => $message_type,
            'destination' => $destination,
            'headers'=> $headers
        ]);
    }
}

/** End of ErrorLogScriber **/
