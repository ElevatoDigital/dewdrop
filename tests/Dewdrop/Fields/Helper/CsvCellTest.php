<?php

namespace Dewdrop\Fields\Helper;

use Dewdrop\Fields\Helper\CsvCell;
use PHPUnit_Framework_TestCase;

class CsvCellTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CsvCell
     */
    protected $csvCellFieldsHelper;

    protected function setUp()
    {
        $this->csvCellFieldsHelper = new CsvCell();
    }

    public function testUsage()
    {
        $this->assertInstanceOf(
            '\Dewdrop\Fields\Helper\CsvCell\Content',
            $this->csvCellFieldsHelper->getContentRenderer()
        );

        $this->assertInstanceOf(
            '\Dewdrop\Fields\Helper\CsvCell\Header',
            $this->csvCellFieldsHelper->getHeaderRenderer()
        );
    }
}
