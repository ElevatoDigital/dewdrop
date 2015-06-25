<?php

namespace Dewdrop\Fields\Helper;

use Dewdrop\View\View;

class TableCellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TableCell
     */
    private $helper;

    public function setUp()
    {
        $view = new View();

        $this->helper = new TableCell($view);
    }

    public function testCanRetrieveContentRenderer()
    {
        $this->assertInstanceOf(
            'Dewdrop\Fields\Helper\TableCell\Content',
            $this->helper->getContentRenderer()
        );
    }

    public function testCanRetrieveHeaderRenderer()
    {
        $this->assertInstanceOf(
            'Dewdrop\Fields\Helper\TableCell\Header',
            $this->helper->getHeaderRenderer()
        );
    }

    public function testCanRetrieveTdClassNamesRenderer()
    {
        $this->assertInstanceOf(
            'Dewdrop\Fields\Helper\TableCell\TdClassNames',
            $this->helper->getTdClassNamesRenderer()
        );
    }

    public function testContentRendererGetsView()
    {
        $this->assertInstanceOf(
            'Dewdrop\View\View',
            $this->helper->getContentRenderer()->getView()
        );
    }

    public function testHeaderRendererGetsView()
    {
        $this->assertInstanceOf(
            'Dewdrop\View\View',
            $this->helper->getHeaderRenderer()->getView()
        );
    }

    public function testTdClassNamesRendererGetsView()
    {
        $this->assertInstanceOf(
            'Dewdrop\View\View',
            $this->helper->getTdClassNamesRenderer()->getView()
        );
    }
}
