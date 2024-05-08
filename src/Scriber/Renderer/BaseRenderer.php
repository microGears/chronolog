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

use Chronolog\Helper\StringHelper;
use Chronolog\LogRecord;

use DateTimeImmutable;

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
     * Flag to remove(or not) HTML/PHP tags when normalizing strings values
     *
     * @var boolean Simple
     */
    protected bool $strip_tags = false;

    /**
     * DateTime format
     *
     * @var string Simple
     */
    protected string $format;


    public function render(LogRecord $record): mixed
    {
        $clone = $record->fork();
        if ($this->getFormat() != $clone->datetime->getFormat()) {
            $clone->datetime->setFormat($this->getFormat());
        }

        return $this->normalizeArray($clone->toArray(), $this->strip_tags);
    }

    protected function normalizeArray($array, $strip_tags = false)
    {
        $normalizedArray = [];

        foreach ($array as $key => $value) {
            if (is_scalar($value) || $value === null) {

                if (is_float($value)) {
                    $value =  (float) $value;
                    if (is_infinite($value)) {
                        $value = ($value > 0 ? '' : '-') . 'INF';
                    } else if (is_nan($value)) {
                        $value = 'NaN';
                    }
                } else {
                    if (is_string($value)) {
                        if ($strip_tags)
                            $value = strip_tags($value);
                        $value = StringHelper::clearInvisibleChars($value, false);
                    }
                }

                $normalizedArray[$key] = $value;
            } else if (is_array($value)) {
                $normalizedArray[$key] = $this->normalizeArray($value, $strip_tags);
            } else if ($value instanceof \DateTimeInterface) {
                if ($value instanceof DateTimeImmutable) {
                    $value = (string) $value;
                } else
                    $value = $value->format($this->getFormat());
                $normalizedArray[$key] = $value;
            } else if (is_object($value)) {
                $className = get_class($value);

                if ($value instanceof \Stringable) {
                    $value = $value->__toString();
                } else
                    $value = $this->normalizeArray(get_object_vars($value), $strip_tags);

                $normalizedArray[$key] = [$className => $value];
            } else if (is_resource($value)) {
                $normalizedArray[$key] = sprintf('[resource(%s)]', get_resource_type($value));
            } else {
                $normalizedArray[$key] = '[unknown(' . gettype($value) . ')]';
            }
        }

        return $normalizedArray;
    }

    /**
     * Get simple
     *
     * @return  boolean
     */
    public function getStripTags(): bool
    {
        return $this->strip_tags;
    }

    /**
     * Set simple
     *
     * @param  boolean  $strip_tags  Simple
     *
     * @return  self
     */
    public function setStripTags(bool $strip_tags): self
    {
        $this->strip_tags = $strip_tags;

        return $this;
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
