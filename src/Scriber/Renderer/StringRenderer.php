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

use Chronolog\Helper\JsonHelper;
use Chronolog\Helper\StringHelper;
use Chronolog\LogRecord;

/**
 * StringRenderer
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 08.05.2024 09:13:19
 */
class StringRenderer extends BaseRenderer
{
    public const PATTERN = "[%datetime%]: %track% %severity_name% %message% %assets%\n";

    protected string $pattern = '';
    public function render(LogRecord $record): mixed
    {
        $vars = parent::render($record);

        $output = $this->getPattern();
        foreach ($vars['assets'] as $var => $val) {
            if (false !== strpos($output, '%assets.'.$var.'%')) {
                $output = str_replace('%assets.'.$var.'%', $this->asString($val), $output);
                unset($vars['assets'][$var]);
            }
        }

        // if (count($vars['assets']) === 0) {
        //     unset($vars['assets']);
        //     $output = str_replace('%assets%', '', $output);
        // }

        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%'.$var.'%')) {
                $output = str_replace('%'.$var.'%', $this->asString($val), $output);
            }
        }        

        return $output;
    }

    protected function asString(mixed $data): string
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        return StringHelper::clearNewlines(JsonHelper::encode($data));
    }

    /**
     * Get the value of format
     */
    public function getPattern(): string
    {
        if ($this->pattern === '') {
            $this->pattern = static::PATTERN;
        }
        return $this->pattern;
    }

    /**
     * Set the value of format
     *
     * @return  self
     */
    public function setPattern($format): self
    {
        $this->pattern = $format;

        return $this;
    }
}
/** End of StringRenderer **/
