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
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 18.05.2024 17:30:00
 */
class BufferedScriber extends ScriberAbstract
{
    protected ?ScriberAbstract $scriber = null;
    protected array $buffer  = [];
    protected int $max_items = 0;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        register_shutdown_function([$this, 'write']);
    }

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

        return $this;
    }
    
    public function getBufferSize(): int
    {
        return count($this->buffer);
    }
}    
/** End of BufferedScriber **/
