<?php

namespace Dewdrop\Fields\Helper;

use Zend\Escaper\Escaper;

class TableCellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TableCell
     */
    private $helper;

    public function setUp()
    {
        $escaper = new Escaper();

        $this->helper = new TableCell($escaper);
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

    public function testContentRendererGetsEscaper()
    {
        $this->assertInstanceOf(
            'Zend\Escaper\Escaper',
            $this->helper->getContentRenderer()->getEscaper()
        );
    }

    public function testHeaderRendererGetsEscaper()
    {
        $this->assertInstanceOf(
            'Zend\Escaper\Escaper',
            $this->helper->getHeaderRenderer()->getEscaper()
        );
    }

    public function testTdClassNamesRendererGetsEscaper()
    {
        $this->assertInstanceOf(
            'Zend\Escaper\Escaper',
            $this->helper->getTdClassNamesRenderer()->getEscaper()
        );
    }
}
