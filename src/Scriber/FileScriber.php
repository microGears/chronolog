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

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if ($this->write_immediately === false) {
            register_shutdown_function([$this, 'write']);
        }
    }

    public function handle(LogEntity $record): bool
    {
        $result         = true;
        $this->buffer[] = $this->getRenderer()->render($record);

        if ($this->write_immediately) {
            $this->write();
        }

        if ($this->getCollaborative()) {
            $result = false;
        }

        return $result;
    }

    public function getFileName()
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
                        /** clean old data */
                        if ($fp = @fopen($file, FileHelper::FOPEN_WRITE_CREATE_DESTRUCTIVE)) {                            
                            fclose($fp);
                        }
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

    public function getPath()
    {
        $result = FileHelper::prepLocation($this->path);

        if ($result && !is_dir($result)) {
            if (@mkdir($result, FileHelper::DIR_WRITE_MODE, true) == false) {
                throw new RuntimeException(sprintf('%s: failed creating folder "%s"', __METHOD__, $result));
            }
        }

        return $result;
    }

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
        if (!$handle = @fopen($file, FileHelper::FOPEN_WRITE_CREATE)) {
            throw new RuntimeException(sprintf('%s: failed to open file "%s"', __METHOD__, $file));
        }

        flock($handle, LOCK_EX);
        fwrite($handle, $data);
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        @chmod($file, FileHelper::FILE_WRITE_MODE);

        if ($this->getDataSize() > 0) {
            $this->write();
        }
    }

    public function getData(bool $flush = false): string
    {
        $result = implode(PHP_EOL, $this->buffer) . PHP_EOL;
        if ($flush) {
            $this->buffer = [];
        }

        return $result;
    }

    public function getDataSize(): int
    {
        $size = 0;
        foreach ($this->buffer as $item) {
            $size += mb_strlen($item) + mb_strlen(PHP_EOL);
        }
        return $size;
    }

    public static function createInstance(string $path, ?string $basename = null, int $size_threshold = 0, int $max_files = 0, bool $write_immediately = false, Severity $severity = Severity::Debug): self
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
