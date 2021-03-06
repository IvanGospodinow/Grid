<?php
namespace GridTest\DataTypeTest;

use Grid\Grid;
use Grid\Column\Column;
use Grid\Row\BodyRow;
use Grid\DataType\Integer;

use PHPUnit\Framework\TestCase;

class IntegerTest extends TestCase
{
    public function testDate()
    {
        $column = new Column(['name' => 'test']);
        $row = new BodyRow([]);
        $filter = new Integer;
        $this->assertTrue(is_int($filter->filter('test', $column, $row)));
        $this->assertTrue(is_int($filter->filter('2.01', $column, $row)));
        $this->assertTrue(is_int($filter->filter('1', $column, $row)));
    }
}