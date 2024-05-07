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
use Chronolog\Scriber\Renderer\RendererInterface;
use Chronolog\SeverityTrait;

/**
 * ScriberAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 12:03:03
 */
abstract class ScriberAbstract extends AutoInitialized implements ScriberInterface
{
    use SeverityTrait;

    protected RendererInterface $renderer;

    /**
     * Get the value of renderer
     */
    public function getRenderer(): RendererInterface
    {
        return $this->renderer;
    }

    /**
     * Set the value of renderer
     *
     * @return  self
     */
    public function setRenderer(RendererInterface $renderer): self
    {
        $this->renderer = $renderer;

        return $this;
    }

}
/** End of ScriberAbstract **/
