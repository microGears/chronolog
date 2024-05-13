<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Chronolog\Scriber\Renderer;

use Chronolog\Helper\PathHelper;
use Chronolog\Helper\StringHelper;
use Chronolog\LogEntity;
use Chronolog\Utilits;
use DateTimeImmutable;
use Stringable;
use Throwable;

/**
 * BaseRenderer
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 18:47:07
 */
class BaseRenderer extends RendererAbstract
{
    public const FORMAT = "Y-m-d\TH:i:sP";
    /**
     * DateTime format
     *
     * @var string Simple
     */
    protected ?string $format = null;
    protected bool $allow_stringify = true;
    protected ?string $base_path = null;

    public function render(LogEntity $record): mixed
    {
        $clone = $record->fork();
        if ($this->getFormat() != $clone->datetime->getFormat()) {
            $clone->datetime->setFormat($this->getFormat());
        }

        return $this->formalizeArray($clone->toArray());
    }

    public function formalizeArray(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_scalar($value) || $value === null) {

                if (is_float($value)) {
                    $value =  (float) $value;
                    if (is_infinite($value)) {
                        $value = ($value > 0 ? '' : '-') . 'INF';
                    } else if (is_nan($value)) {
                        $value = 'NaN';
                    }
                } 

                $result[$key] = $value;

            } else if (is_array($value)) {
                $result[$key] = $this->formalizeArray($value);
            } else if ($value instanceof \DateTimeInterface) {
                if ($value instanceof DateTimeImmutable) {
                    $value = (string) $value;
                } else
                    $value = $value->format($this->getFormat());
                $result[$key] = $value;
            } else if (is_object($value)) {
                $result[$key] = $this->formalizeObject($value);
            } else if (is_resource($value)) {
                /** type `resource` isn't supported by serialization */
                $result[$key] = 0;
            } else {
                $result[$key] = '[unknown(' . gettype($value) . ')]';
            }
        }

        return $result;
    }

    public function formalizeObject(object $object): mixed
    {
        $result = [];
        if (is_object($object)) {
            if ($object instanceof Throwable) {
                return $this->formalizeException($object);
            }

            if ($this->allow_stringify == true && $object instanceof Stringable) {
                return $object->__toString();
            }

            $result = $this->formalizeArray(get_object_vars($object));
            $result['class'] = get_class($object);

            ksort($result);
        }
        return $result;
    }

    public function formalizeException(Throwable $thr): mixed
    {
        $result = [
            'class' => get_class($thr),
            'message' => $thr->getMessage(),
            'code' => (int) $thr->getCode(),
            'file' => PathHelper::overlapPath($thr->getFile(),$this->base_path) . ':' . $thr->getLine(),
        ];

        $trace = $thr->getTrace();
        foreach ($trace as $frame) {
            if (isset($frame['file'], $frame['line'])) {
                $result['trace'][] = $frame['file'] . ':' . $frame['line'];
            }
        }

        if (($previous = $thr->getPrevious()) instanceof Throwable) {
            $result['previous'] = $this->formalizeException($previous);
        }

        return $result;
    }

    /**
     * Get simple
     *
     * @return  string
     */
    public function getFormat(): string
    {
        if ($this->format === null) {
            $this->format = static::FORMAT;
        }
        return $this->format;
    }

    /**
     * Set simple
     *
     * @param  string  $format  Simple
     *
     * @return  self
     */
    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }
}
/** End of BaseRenderer **/
