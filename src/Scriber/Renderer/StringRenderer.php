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

use Chronolog\Helper\ArrayHelper;
use Chronolog\Helper\JsonHelper;
use Chronolog\Helper\PathHelper;
use Chronolog\Helper\StringHelper;
use Chronolog\LogEntity;
use Throwable;

/**
 * StringRenderer
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 08.05.2024 09:13:19
 */
class StringRenderer extends BaseRenderer
{
    public const PATTERN = "[%datetime%]: %track% %severity_name% %message% %assets%\n";

    /**
     * @var string|null $pattern The pattern used by the StringRenderer.
     */
    protected ?string $pattern = null;

    /**
     * Determines whether multiline rendering is allowed.
     *
     * @var bool $allow_multiline
     */
    protected bool $allow_multiline = false;

    /**
     * The maximum length of a row in the string renderer.
     * Leave 0 to not limit the line size
     * 
     * Selecting the optimal log file line length to ensure readability:
     * - 256 to 512 characters: system log files.    
     * - 512 to 1024 characters: application log files.
     * - 1024 to 2048 characters: log files for debugging and troubleshooting.
     * 
     * @var int
     */
    protected int $row_max_length = 0;
    
    /**
     * The replacement string used when a row is oversize in the StringRenderer class.
     *
     * @var string
     */
    protected string $row_oversize_replacement = '...';

    /**
     * Renders the log entity as a string.
     *
     * @param LogEntity $entity The log entity to render.
     * @return mixed The rendered log entity.
     */
    public function render(LogEntity $entity): mixed
    {
        $vars = parent::render($entity);

        $output = $this->getPattern();
        foreach ($vars['assets'] as $var => $val) {
            if (false !== strpos($output, '%assets.' . $var . '%')) {
                $output = str_replace('%assets.' . $var . '%', $this->stringify($val), $output);
                unset($vars['assets'][$var]);
            }
        }

        if (count($vars['assets']) === 0) {
            unset($vars['assets']);
            $output = str_replace('%assets%', '', $output);
        }

        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%' . $var . '%')) {
                $output = str_replace('%' . $var . '%', $this->stringify($val), $output);
            }
        }

        if (false !== strpos($output, '%')) {
            $output = preg_replace('/%(?:[^%]+)\.?.+?%/', '', $output);
            $output = preg_replace('/\s{2}/', ' ', $output);

            /** @todo Is this really necessary? */
            if ($output === null) {
                StringHelper::throwPregError(preg_last_error());
            }
        }

        if($this->row_max_length > 0) {
            $output = StringHelper::limitLength($output, $this->row_max_length, $this->row_oversize_replacement);
        }
        
        return $output;
    }

    /**
     * Converts the given data into a string representation.
     *
     * @param mixed $data The data to be converted.
     * @return string The string representation of the data.
     */
    public function stringify(mixed $data): string
    {
        $result = $data;
        if (is_scalar($data) || null === $data) {
            if (!is_string($data))
                $result = var_export($data, true);
        } else
            $result = JsonHelper::encode($data);

        if ($this->allow_multiline) {
            $result = preg_replace('/(?<!\\\\)\\\\[rn]/', "\n", $result);
            $result = preg_replace('/(?<!\\\\)\\\\[t]/', "\t", $result);

            /** @todo Is this really necessary? */
            if ($result === null) {
                StringHelper::throwPregError(preg_last_error());
            }
            return $result;
        }

        return StringHelper::clearCRLF($result);
    }

    /**
     * Get the value of format
     */
    public function getPattern(): string
    {
        if ($this->pattern === null) {
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

    /**
     * Formalizes an exception into a string representation.
     *
     * @param Throwable $thr The exception to be formalized.
     * @return mixed The formalized string representation of the exception.
     */
    public function formalizeException(Throwable $thr): mixed
    {
        $result = StringHelper::className($thr, !$this->allow_fullnamespace) . ' #' . $thr->getCode() . ': ' . $thr->getMessage() . ' at ' . PathHelper::overlapPath($thr->getFile(), $this->base_path) . ':' . $thr->getLine();
        if ($this->include_traces) {
            $result .= "\n" . $this->formalizeTrace($thr->getTrace());
        }

        if (($previous = $thr->getPrevious()) instanceof Throwable) {            
            $result .= "\n[previous] ".$this->formalizeException($previous);
        }

        return $result;
    }

    /**
     * Formalizes the given trace array.
     *
     * @param array $trace The trace array to be formalized.
     * @return mixed The formalized trace.
     */
    public function formalizeTrace(array $trace): mixed
    {
        $result = "[backtrace]\n";
        $pad = strlen(count($trace)) + 1;
        foreach ($trace as $key => $value) {
            $result .= sprintf("#%-{$pad}d", $key);

            if (($class = ArrayHelper::element('class', $value))) {
                $result .= StringHelper::className($class, !$this->allow_fullnamespace);
                $result .= ArrayHelper::element('type', $value, '::');
            }

            if ($function = ArrayHelper::element('function', $value)) {
                $result .= $function . '()';
            }

            if ($file = ArrayHelper::element('file', $value)) {
                $result .= ' in ' . PathHelper::overlapPath($file, $this->base_path);
                if ($line = ArrayHelper::element('line', $value)) {
                    $result .= ':' . $line;
                }
            }

            $result .= "\n";
        }
        return $result;
    }
}
/** End of StringRenderer **/
