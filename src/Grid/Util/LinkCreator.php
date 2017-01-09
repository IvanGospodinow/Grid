<?php

namespace Grid\Util;

use Grid\Util\Traits\GridAwareTrait;
use Grid\Interfaces\GridInterface;
use Grid\Interfaces\LinksInterface;

/**
 * @author Ivan Gospodinow <ivangospodinow@gmail.com>
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
        $result = '';
        foreach ($this->getGrid()[LinksInterface::class] as $plugin) {
            $result = call_user_func_array([$plugin, $name], $arguments);
            if (!empty($result)) {
                break;
            }
        }
        return $result;
    }
}
