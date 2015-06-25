<?php

namespace Dewdrop\Mail\View\Helper;

use Dewdrop\Mail\View\View;
use PHPUnit_Framework_TestCase;

class DocumentTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->view = new View();
    }

    public function testOpenMethodStartsHtmlDocument()
    {
        $open = $this->view->document()->open('TEST_TITLE');

        $this->assertContains('<!DOCTYPE ', $open);
        $this->assertContains('<html ', $open);
    }

    public function testCloseMethodEndsHtmlDocument()
    {
        $close = $this->view->document()->close();
        $this->assertContains('</html>', $close);
    }

    public function testOpenMethodRendersSuppliedTitle()
    {
        $open = $this->view->document()->open('TEST_TITLE');

        $this->assertContains('<title>TEST_TITLE</title>', $open);
    }

    public function testOpenMethodEscapesSuppliedTitle()
    {
        $open = $this->view->document()->open('TEST&TITLE');

        $this->assertContains('<title>TEST&amp;TITLE</title>', $open);
    }
}

