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

/**
 * JsonRenderer
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 16.05.2024 14:00:00
 */
class JsonRenderer extends BaseRenderer
{
    protected ?int $flags = null;
    
    public function render(LogEntity $entity): mixed
    {
        $vars = parent::render($entity);
        if (count($vars['assets']) === 0) {
            unset($vars['assets']);
        }
        return JsonHelper::encode($vars, $this->flags);
    }

    /**
     * Get the value of encoding flags
     */ 
    public function getFlags():int
    {
        return $this->flags;
    }

    /**
     * Set the value of encoding flags
     *
     * @return  self
     */ 
    public function setFlags(int $flags):self
    {
        $this->flags = $flags;

        return $this;
    }
}
/** End of JsonRenderer **/
