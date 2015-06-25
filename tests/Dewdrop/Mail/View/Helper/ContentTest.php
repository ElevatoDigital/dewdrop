<?php

namespace Dewdrop\Mail\View\Helper;

use Dewdrop\Mail\View\View;
use PHPUnit_Framework_TestCase;

class ContentTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->view = new View();
    }

    public function testOpenMethodOpensDivAndTable()
    {
        $open = $this->view->content()->open();

        $this->assertContains('<div', $open);
        $this->assertContains('<table', $open);
    }

    public function testCloseMethodClosesDivAndTable()
    {
        $close = $this->view->content()->close();

        $this->assertContains('</div>', $close);
        $this->assertContains('</table>', $close);
    }
}

