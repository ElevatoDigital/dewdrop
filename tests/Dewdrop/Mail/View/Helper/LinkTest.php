<?php

namespace Dewdrop\Mail\View\Helper;

use Dewdrop\Mail\View\View;
use PHPUnit_Framework_TestCase;

class LinkTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->view = new View();
    }

    public function testSuppliedContentIsIncluded()
    {
        $this->assertContains('TEST_CONTENT', $this->view->a('TEST_CONTENT', 'http://example.org/'));
    }

    public function testSuppliedHrefIsUsedAndEscaped()
    {
        $this->assertContains(
            'href="' . $this->view->escapeHtmlAttr('http://example.org/') . '"',
            $this->view->a('TEST_CONTENT', 'http://example.org/')
        );
    }
}

