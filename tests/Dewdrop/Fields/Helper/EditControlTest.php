<?php

namespace Dewdrop\Fields\Helper;

use Dewdrop\View\View;

class EditControlTest extends \PHPUnit_Framework_TestCase
{
    private $helper;

    public function setUp()
    {
        $view = new View();

        $this->helper = new EditControl($view);
    }

    public function testCanRetrieveControlRenderer()
    {
        $this->assertInstanceOf(
            '\Dewdrop\Fields\Helper\EditControl\Control',
            $this->helper->getControlRenderer()
        );
    }

    public function testCanRetrieveLabelRenderer()
    {
        $this->assertInstanceOf(
            '\Dewdrop\Fields\Helper\EditControl\Label',
            $this->helper->getLabelRenderer()
        );
    }
}
