<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Test\BaseTestCase;
use Dewdrop\View\View;

class HeadLinkTest extends BaseTestCase
{
    /**
     * @var View
     */
    protected $view;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->view = new View();
    }

    /**
     * @return void
     */
    public function testGetInstanceFromView()
    {
        $headLink = $this->view->headLink();

        $this->assertInstanceOf('\Zend\View\Helper\HeadLink', $headLink);
    }
}