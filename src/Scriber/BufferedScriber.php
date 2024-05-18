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

use Chronolog\AutoInitialized;
use Chronolog\Helper\StringHelper;
use Chronolog\LogEntity;
use RuntimeException;

/**
 * BufferedScriber
 * Formally, it is a wrapper class for other scribes. The main purpose is to buffer log entries and process them in portions.
 * For example, if you limit the buffer size to 10 records:
 *  ...
 *  $scriber = new BufferedScriber();
 *  $scriber->setMaxItems(10);
 *  ...
 * 
 * It will accumulate records until the buffer reaches 10 and then start processing. 
 * The rest of the entries in the buffer will be processed at the end of the script
 *
 * 
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 18.05.2024 17:30:00
 */
class BufferedScriber extends ScriberAbstract
{
    /**
     * @var ScriberAbstract|null $scriber The buffered scriber instance.
     */
    protected ?ScriberAbstract $scriber = null;

    /**
     * @var array $buffer The buffer used to store data.
     */
    protected array $buffer  = [];

    /**
     * The maximum number of elements that can be stored in the buffer before processing.
     * The default is 0, i.e. buffering disabled
     *
     * @var int
     */
    protected int $max_items = 0;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        register_shutdown_function([$this, 'write']);
    }

    /**
     * Handles a log entity.
     *
     * @param LogEntity $entity The log entity to handle.
     * @return bool Returns true if the log entity was successfully handled, false otherwise.
     */
    public function handle(LogEntity $entity): bool
    {
        if ($this->isAllowedSeverity($entity) === false) {
            return false;
        }

        $this->buffer[] = $entity->fork();
        if (count($this->buffer) >= $this->max_items) {
            $this->write();
        }

        if ($this->getCollaborative()) {
            return false;
        }

        return true;
    }

    /**
     * Writes the buffered entities.
     *
     * @return void
     */
    protected function write(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        if ($this->scriber === null) {
            throw new RuntimeException(sprintf('%s: failed writing; scriber is not set', __METHOD__));
        }

        foreach ($this->buffer as $entity) {
            $this->scriber->handle($entity);
        }

        $this->buffer = [];
    }

    /**
     * Get the value of max_items
     */
    public function getMaxItems(): int
    {
        return $this->max_items;
    }

    /**
     * Set the value of max_items
     *
     * @return  self
     */
    public function setMaxItems(int $value): self
    {
        $this->max_items = $value;

        return $this;
    }

    /**
     * Get the value of scriber
     */
    public function getScriber(): ScriberAbstract
    {
        return $this->scriber;
    }

    /**
     * Set the value of scriber
     *
     * @return  self
     */
    public function setScriber(mixed $scriber): self
    {
        if (!$scriber instanceof ScriberAbstract) {
            $scriber = AutoInitialized::turnInto($scriber);
        }

        if (!$scriber instanceof ScriberInterface || $scriber instanceof self) {
            throw new RuntimeException(sprintf('%s: failed setting scriber; invalid scriber instance "%s"', __METHOD__, StringHelper::className($scriber, true)));
        }

        $this->scriber = $scriber;
        if ($scriber instanceof ScriberAbstract) {
            $this->setSeverity($scriber->getSeverity()); // mimic the severity of the wrapped scriber
        }

        return $this;
    }

    public function getBufferSize(): int
    {
        return count($this->buffer);
    }

    /**
     * Create a new instance of the BufferedScriber class.
     *
     * @param mixed $scriber The scriber object.
     * @param int $max_items The maximum number of items to buffer (optional, default is 0).
     * @return self The new instance of the BufferedScriber class.
     */
    public static function createInstance(mixed $scriber, int $max_items = 0): self
    {
        return new BufferedScriber(['scriber' => $scriber, 'max_items' => $max_items]);
    }
}
/** End of BufferedScriber **/
