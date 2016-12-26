<?php
namespace Grid\DataType;

use Grid\GridRow;
use Grid\Column\AbstractColumn;

/**
 *
 * @author Gospodinow
 */
class Str implements DataTypeInterface
{
    public function filter($value, AbstractColumn $column, GridRow $contex)
    {
        if (is_object($value)
        && !method_exists($value, '__toString')) {
            return '';
        }
        return (string) $value;
    }
}