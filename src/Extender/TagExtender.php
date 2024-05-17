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
use Chronolog\LogEntity;

/**
 * TagExtender
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 10:05:42
 */
class TagExtender extends ExtenderAbstract
{
    private mixed $tags;

    public function __invoke(LogEntity $entity): LogEntity
    {
        $entity->assets['tags'] = $this->tags;
        return $entity;
    }

    /**
     * Get the tags for the extender.
     *
     * @return array
     */ 
    public function getTags(): mixed
    {
        return $this->tags;
    }

    /**
     * Set the tags for the extender.
     *
     * @param array $tags The tags to set.
     * @return self
     */
    public function setTags(mixed $tags = []): self
    {
        $this->tags = $tags;

        return $this;
    }
}
/** End of TagExtender **/
