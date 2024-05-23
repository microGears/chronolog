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

use Chronolog\Helper\FileHelper;
use Chronolog\LogEntity;
use Chronolog\Scriber\Renderer\StringRenderer;
use Chronolog\Scriber\ScriberAbstract;
use Chronolog\Severity;
use RuntimeException;

/**
 * FileScriber
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 14.05.2024 16:07:16
 */
class FileScriber extends ScriberAbstract
{
    const FILE_EXT      = 'log';
    const FILE_BASENAME = 'log';

    protected ?string $basename   = null;
    protected ?string $path       = null;
    protected string $ext         = self::FILE_EXT;
    protected int $size_threshold = 0;
    protected int $max_files      = 0;
    protected array $buffer           = [];
    protected bool $write_immediately = false;

    /**
     * Handles a log record.
     *
     * @param LogEntity $entity The log entity to handle.
     * @return bool Returns true if the log entity was successfully handled, false otherwise.
     */
    public function handle(LogEntity $entity): bool
    {
        if ($this->isAllowedSeverity($entity) === false) {
            return false;
        }

        $result         = true;
        $this->buffer[] = $this->getRenderer()->render($entity);

        if ($this->write_immediately) {
            $this->write();
        }

        if ($this->getCollaborative()) {
            $result = false;
        }

        return $result;
    }

    /**
     * Returns the file name associated with this FileScriber instance.
     *
     * @return string The file name.
     */
    public function getFileName(): string
    {
        $result = $this->basename;

        if (is_null($result)) {
            $result = self::FILE_BASENAME;
        }

        $result    = pathinfo($result, PATHINFO_FILENAME);
        $ext       = ($ext = pathinfo($result, PATHINFO_EXTENSION)) ? $ext : $this->ext;
        $location  = $this->getPath();
        $size_data = $this->getDataSize();
        $exists    = 0;
        $count     = 0;

        // fetch index
        $files = glob("$location/*.$ext");
        natsort($files);
        foreach ($files as $file) {
            if (preg_match('~(' . $result . ')\_([\d]+)\.' . $ext . '$~', $file, $matched)) {
                $exists = $matched[2];
                if ($this->size_threshold > 0) {
                    if (filesize($file) + $size_data <= $this->size_threshold) {
                        $count = $exists;
                        break;
                    }
                }
            }
        }

        // index undefined
        if ($count == 0) {
            if (($limit = $this->max_files)) {
                $reference  = range(1, $limit);
                $files_logs = [];
                $files      = glob("$location/*.$ext");
                natsort($files);
                foreach ($files as $file) {
                    if (preg_match('~(' . $result . ')\_([\d]+)\.' . $ext . '$~', $file, $matched)) {
                        $suffix                       = $matched[2];
                        $files_logs[filemtime($file)] = $suffix;

                        // exclude index
                        unset($reference[array_search($suffix, $reference)]);
                    }
                }

                if (empty($reference)) {
                    ksort($files_logs);
                    $count = reset($files_logs);
                    if (file_exists($file = FileHelper::prepFilename($location . '/' . $result . '_' . $count . '.' . $ext))) {
                        FileHelper::flushFile($file);
                    }
                } else {
                    $count = reset($reference);
                }
            } else {
                $count = ++$exists;
            }
        }

        $result = FileHelper::prepFilename($location . '/' . $result . '_' . $count . '.' . $ext);

        return $result;
    }

    /**
     * Get the path of the file.
     *
     * @return string The path of the file.
     */
    public function getPath(): string
    {
        $result = FileHelper::prepLocation($this->path);

        if ($result && !is_dir($result)) {
            if (@mkdir($result, FileHelper::DIR_WRITE_MODE, true) == false) {
                throw new RuntimeException(sprintf('%s: failed creating folder "%s"', __METHOD__, $result));
            }
        }

        return $result;
    }

    /**
     * Set the value of path
     *
     * @return  self
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Writes the content to the file.
     *
     * @return void
     */
    protected function write(): void
    {
        if (count($this->buffer) == 0) {
            return;
        }

        $data = "";
        if ($this->size_threshold > 0 && (($data_size = $this->getDataSize()) > $this->size_threshold)) {
            while ($this->size_threshold > mb_strlen($data)) {
                $data .= array_shift($this->buffer) . PHP_EOL;
            }
        }

        if (empty($data)) {
            $data = $this->getData(true);
        }

        $file = $this->getFileName();
        FileHelper::writeFile($file, $data, FileHelper::FOPEN_WRITE_CREATE);

        if ($this->getDataSize() > 0) {
            $this->write();
        }
    }

    /**
     * Retrieves the data from the FileScriber.
     *
     * @param bool $flush Whether to flush the data after retrieval. Default is false.
     * @return string The retrieved data.
     */
    public function getData(bool $flush = false): string
    {
        $result = implode(PHP_EOL, $this->buffer) . PHP_EOL;
        if ($flush) {
            $this->buffer = [];
        }

        return $result;
    }

    /**
     * Returns the size of the data.
     *
     * @return int The size of the data.
     */
    public function getDataSize(): int
    {
        $size = 0;
        foreach ($this->buffer as $item) {
            $size += mb_strlen($item) + mb_strlen(PHP_EOL);
        }
        return $size;
    }

    /**
     * Get the value of basename
     */
    public function getBasename(): string
    {
        return $this->basename;
    }

    /**
     * Set the value of basename
     *
     * @return  self
     */
    public function setBasename(?string $basename): self
    {
        $this->basename = $basename;

        return $this;
    }


    /**
     * Get the value of ext
     */
    public function getExt(): string
    {
        return $this->ext;
    }

    /**
     * Set the value of ext
     *
     * @return  self
     */
    public function setExt(string $ext): self
    {
        $this->ext = $ext;

        return $this;
    }

    /**
     * Get the size threshold for the file scriber.
     *
     * @return int The size threshold value.
     */
    public function getSizeThreshold(): int
    {
        return $this->size_threshold;
    }


    /**
     * Sets the size threshold for the file scriber.
     *
     * @param int $value The size threshold value.
     * @return self
     */
    public function setSizeThreshold(int $value): self
    {
        $this->size_threshold = $value;

        return $this;
    }

    /**
     * Get the maximum number of files to keep.
     *
     * @return int The maximum number of files to keep.
     */
    public function getMaxFiles(): int
    {
        return $this->max_files;
    }

    /**
     * Sets the maximum number of files to keep.
     *
     * @param int $max_files The maximum number of files to keep.
     * @return self
     */
    public function setMaxFiles(int $max_files): self
    {
        $this->max_files = $max_files;

        return $this;
    }

    /**
     * Get the value indicating whether the file should be written immediately.
     *
     * @return bool The value indicating whether the file should be written immediately.
     */
    public function getWriteImmediately(): bool
    {
        return $this->write_immediately;
    }

    /**
     * Sets the flag indicating whether to write immediately or buffer the output.
     *
     * @param bool $value The value indicating whether to write immediately or buffer the output.
     * @return self
     */
    public function setWriteImmediately(bool $value): self
    {
        $this->write_immediately = $value;

        if ($this->write_immediately === false) {
            register_shutdown_function([$this, 'write']);
        }

        return $this;
    }

    /**
     * Creates a new instance of the FileScriber class.
     *
     * @param string $path The path to the log file.
     * @param string|null $basename The basename of the log file. Defaults to null.
     * @param int $size_threshold The maximum size of the log file in bytes before it is rotated. Defaults to 0.
     * @param int $max_files The maximum number of rotated log files to keep. Defaults to 0.
     * @param bool $write_immediately Whether to write log messages immediately or buffer them. Defaults to false.
     * @param Severity|array $severity The severity level(s) to log. Defaults to Severity::Debug.
     * @return self The newly created instance of the FileScriber class.
     */
    public static function createInstance(string $path, ?string $basename = null, int $size_threshold = 0, int $max_files = 0, bool $write_immediately = false, Severity|array $severity = Severity::Debug): self
    {
        return new FileScriber([
            'severity' => $severity,
            'renderer' => new StringRenderer([
                'pattern' => "[%datetime%]: %track%~%severity_name% %message% %assets%",
                'allow_multiline' => true,
                'include_traces' => true,
            ]),
            'path' => $path,
            'basename' => $basename,
            'size_threshold' => $size_threshold,
            'max_files' => $max_files,
            'write_immediately' => $write_immediately
        ]);
    }
}
/** End of FileScriber **/
