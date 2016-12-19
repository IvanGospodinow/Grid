<?php

namespace Grid\Util;

use Grid\Util\Traits\GridAwareTrait;
use Grid\GridInterface;
use Grid\Interfaces\LinksInterface;

/**
 * @author Gospodinow
 */
class LinkCreator implements GridInterface
{
    use GridAwareTrait;

    /**
     *
     * @param type $name
     * @param type $arguments
     * @return type
     */
    public function __call($name, $arguments)
    {
        $plugins = $this->getGrid()->getObjects(LinksInterface::class);
        $result = '';
        foreach ($plugins as $plugin) {
            $result = call_user_func_array([$plugin, $name], $arguments);
            if (!empty($result)) {
                break;
            }
        }
        return $result;
    }
}