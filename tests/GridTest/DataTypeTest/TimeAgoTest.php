<?php
namespace GridTest\DataTypeTest;

use Grid\Grid;
use Grid\Column\Column;
use Grid\Row\BodyRow;
use Grid\DataType\TimeAgo;

use PHPUnit\Framework\TestCase;

class TimeAgoTest extends TestCase
{
    public function testDate()
    {
        $time = strtotime('-1 hour');
        $column = new Column(['name' => 'test']);
        $row = new BodyRow([]);
        $filter = new TimeAgo;
        $filter->setGrid(new Grid);
        $this->assertTrue(is_string($filter->filter($time, $column, $row)));
        $this->assertTrue(is_string($filter->filter('asfafs', $column, $row)));
        $this->assertTrue(is_string($filter->filter(time(), $column, $row)));
    }
}