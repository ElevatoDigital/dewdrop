<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Test\BaseTestCase;
use Dewdrop\View\View;

class ZendInstanceTest extends BaseTestCase
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
    public function testGetHeadlinkFromView()
    {
        $headLink = $this->view->headLink();

        $this->assertInstanceOf('\Zend\View\Helper\HeadLink', $headLink);
    }

    /**
     * @return void
     */
    public function testGetHeadmetaFromView()
    {
        $headMeta = $this->view->headMeta();

        $this->assertInstanceOf('\Zend\View\Helper\HeadMeta', $headMeta);
    }

    /**
     * @return void
     */
    public function testGetHeadscriptFromView()
    {
        $headScript = $this->view->headScript();

        $this->assertInstanceOf('\Zend\View\Helper\HeadScript', $headScript);
    }

    /**
     * @return void
     */
    public function testGetHeadstyleFromView()
    {
        $headStyle = $this->view->headStyle();

        $this->assertInstanceOf('\Zend\View\Helper\HeadStyle', $headStyle);
    }
}