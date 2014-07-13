<?php

namespace Dewdrop\Mail\View;

use PHPUnit_Framework_TestCase;

class ViewTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->view = new View();
    }

    public function testCustomHelpersCanBeCalled()
    {
        $this->assertInstanceOf('\Dewdrop\Mail\View\Helper\Document', $this->view->document());
    }
}

