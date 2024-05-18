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

use Chronolog\LogEntity;

/**
 * RendererInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 11:15:52
 */
interface RendererInterface
{
    public function render(LogEntity $entity):mixed;
}
/** End of RendererInterface **/