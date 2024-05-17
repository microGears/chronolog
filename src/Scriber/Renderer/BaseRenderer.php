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
    /**
     * Determines whether the renderer allows stringification.
     *
     * @var bool
     */
    protected bool $allow_stringify = true;

    /**
     * Determines whether the full namespace is allowed.
     *
     * @var bool $allow_fullnamespace
     */
    protected bool $allow_fullnamespace = false;

    /**
     * Determines whether traces should be included in the rendered output.
     *
     * @var bool $include_traces
     */
    protected bool $include_traces = true;

    /**
     * Base path (for overlap).
     * $base_path property, which can be represents the base path of the project's files.
     * This property is NULL and can be set to a string value representing the base path 
     * that will be excluded from filenames when rendering(backtrace).
     *
     * @var string|null $base_path The base path for rendering.
     */
    protected ?string $base_path = null;

    /**
     * Renders the log entity.
     *
     * @param LogEntity $entity The log entity to render.
     * @return mixed The rendered log entity.
     */
    public function render(LogEntity $entity): mixed
    {
        $clone = $entity->fork();
        if ($this->getFormat() != $clone->datetime->getFormat()) {
            $clone->datetime->setFormat($this->getFormat());
        }

        return $this->formalizeArray($clone->toArray());
    }

    /**
     * Formalizes an array by ensuring that all keys are integers starting from 0.
     *
     * @param array $array The array to be formalized.
     * @return array The formalized array.
     */
    public function formalizeArray(mixed $array): mixed
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

    /**
     * Formalizes an object.
     *
     * @param object $object The object to be formalized.
     * @return mixed The formalized object.
     */
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
            $result['class'] = StringHelper::className($object, !$this->allow_fullnamespace);

            ksort($result);
        }
        return $result;
    }

    /**
     * Formalizes an exception for rendering.
     *
     * @param Throwable $thr The exception to be formalized.
     * @return mixed The formalized exception.
     */
    public function formalizeException(Throwable $thr): mixed
    {
        $result = [
            'class' => StringHelper::className($thr, !$this->allow_fullnamespace),
            'message' => $thr->getMessage(),
            'code' => (int) $thr->getCode(),
            'file' => PathHelper::overlapPath($thr->getFile(), $this->base_path) . ':' . $thr->getLine(),
        ];

        if ($this->include_traces) {
            $trace = $thr->getTrace();
            foreach ($trace as $frame) {
                if (isset($frame['file'], $frame['line'])) {
                    $result['trace'][] = PathHelper::overlapPath($frame['file'], $this->base_path) . ':' . $frame['line'];
                }
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
