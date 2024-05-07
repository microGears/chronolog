<?php

/**
 * This file is part of chronolog/chronolog.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



namespace Chronolog\Extender;

use Chronolog\Extender\ExtenderAbstract;
use Chronolog\LogRecord;

/**
 * TagExtender
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 10:05:42
 */
class TagExtender extends ExtenderAbstract
{
    private array $tags;

    public function __invoke(LogRecord $record): LogRecord
    {
        $record->assets['tags'] = $this->tags;
        return $record;
    }

    /**
     * Get the value of tags
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Set the value of tags
     *
     * @return  self
     */
    public function setTags(array $tags = []): self
    {
        $this->tags = $tags;

        return $this;
    }
}
/** End of TagExtender **/
