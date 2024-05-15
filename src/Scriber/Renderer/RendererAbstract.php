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

use Chronolog\AutoInitialized;
use Chronolog\LogEntity;

/**
 * RendererAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 11:18:59
 */
abstract class RendererAbstract extends AutoInitialized implements RendererInterface
{
    /**
     * Renders the log entity.
     *
     * @param LogEntity $entity The log entity to render.
     * @return mixed The rendered log entity.
     */
    abstract public function render(LogEntity $entity): mixed;
}
/** End of RendererAbstract **/
