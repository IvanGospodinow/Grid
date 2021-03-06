<?php

namespace Grid\Plugin;

use Grid\Interfaces\RenderPluginInterface;
use Grid\Interfaces\JavascriptCaptureInterface;
use Grid\Interfaces\JavascriptPluginInterface;

/**
 * Adds javascript to the render output
 *
 * @author Ivan Gospodinow <ivangospodinow@gmail.com>
 */
class JavascriptCapturePlugin extends AbstractPlugin implements RenderPluginInterface
{
    /**
     *
     */
    public function preRender(string $html) : string
    {
        return $html;
    }

    /**
     *
     * @param string $html
     */
    public function postRender(string $html) : string
    {
        foreach ($this->getGrid()[JavascriptCaptureInterface::class] as $scriptCapture) {

            $script = (string)
            $this->getGrid()->filter(
               JavascriptPluginInterface::class,
               'addJavascript',
               $scriptCapture
            );

            if (empty($script)) {
                continue;
            }

            $html .= sprintf(
                '%s<script>%s%s%s</script>',
                PHP_EOL,
                PHP_EOL,
                $script,
                PHP_EOL
            );
        }
        return $html;
    }
}
