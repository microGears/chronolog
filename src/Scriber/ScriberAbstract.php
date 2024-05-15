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
use Chronolog\Helper\ArrayHelper;
use Chronolog\Helper\StringHelper;
use Chronolog\LogEntity;
use Chronolog\Scriber\Renderer\BaseRenderer;
use Chronolog\Scriber\Renderer\RendererInterface;
use Chronolog\Severity;
use Chronolog\SeverityTrait;
use RuntimeException;

/**
 * ScriberAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 12:03:03
 */
abstract class ScriberAbstract extends AutoInitialized implements ScriberInterface
{
    use SeverityTrait;

    /**
     * @var RendererInterface|null $renderer The renderer used by the ScriberAbstract class.
     */
    protected ?RendererInterface $renderer = null;

    /**
     * Indicates whether the Scriber is collaborative or not.
     *
     * @var bool $collaborative
     */
    protected bool $collaborative = false;
    
    /**
     * Get the value of renderer
     */
    public function getRenderer(): RendererInterface
    {
        if ($this->renderer === null) {
            $this->renderer = $this->getDefaultRenderer();
        }
        return $this->renderer;
    }


    /**
     * Set the renderer for the Scriber.
     *
     * @param RendererInterface|array $renderer The renderer to set.
     * @return self Returns the instance of the ScriberAbstract class.
     */
    public function setRenderer(RendererInterface|array $renderer): self
    {
        if (is_array($renderer)) {
            $renderer = ArrayHelper::arrayToInstance($renderer);
        }
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Returns the default renderer for the ScriberAbstract class.
     *
     * @return RendererInterface The default renderer.
     */
    public function getDefaultRenderer(): RendererInterface
    {
        return new BaseRenderer();
    }

    /**
     * Get the value of collaborative
     */ 
    public function getCollaborative():bool
    {
        return $this->collaborative;
    }

    /**
     * Set the value of collaborative
     *
     * @return  self
     */ 
    public function setCollaborative(bool $collaborative):self
    {
        $this->collaborative = $collaborative;

        return $this;
    }
}
/** End of ScriberAbstract **/
