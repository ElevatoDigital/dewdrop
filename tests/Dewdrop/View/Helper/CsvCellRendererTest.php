<?php

namespace Dewdrop\View\Helper;

use Dewdrop\View\View;
use PHPUnit_Framework_TestCase;

class CsvCellRendererTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var View
     */
    protected $view;

    protected function setUp()
    {
        $this->view = new View();
    }

    public function testUsage()
    {
        /* @var $csvCellFieldHelper \Dewdrop\Fields\Helper\CsvCell */
        $csvCellFieldHelper = $this->view->csvCellRenderer();

        $this->assertInstanceOf('\Dewdrop\Fields\Helper\CsvCell', $csvCellFieldHelper);
    }
}
