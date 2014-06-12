<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Table;
use Dewdrop\Db\Table\AdminModelInterface;
use Dewdrop\Fields;
use Dewdrop\View\View;
use PHPUnit_Framework_TestCase;

class CsvExportTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Dewdrop\Admin\Component\Silex\CrudAbstract|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $component;

    /**
     * @var CsvExport|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $csvExportViewHelper;

    /**
     * @var View
     */
    protected $view;

    protected function setUp()
    {
        $this->view = new View();

        $this->component = $this->getMockBuilder('\Dewdrop\Admin\Component\Silex\CrudAbstract')
            ->disableOriginalConstructor()
            ->setMethods(['getFields', 'getListing', 'getPrimaryModel'])
            ->getMockForAbstractClass();

        $model = $this->getMockBuilder('\Dewdrop\View\Helper\TestAdminModel')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->component->expects($this->any())
            ->method('getPrimaryModel')
            ->will($this->returnValue($model));

        $this->component->expects($this->any())
            ->method('getFields')
            ->will($this->returnValue(new Fields()));

        $listing = $this->getMockBuilder('\Dewdrop\Fields\Listing')
            ->disableOriginalConstructor()
            ->getMock();

        $listing->expects($this->any())
            ->method('fetchData')
            ->will($this->returnValue([]));

        $this->component->expects($this->any())
            ->method('getListing')
            ->will($this->returnValue($listing));

        /* @var $component \Dewdrop\Admin\Component\Silex\CrudAbstract|\PHPUnit_Framework_MockObject_MockObject */
        $component = $this->component;
        $component
            ->setTitle('title');

        $this->csvExportViewHelper = $this->getMockBuilder('\Dewdrop\View\Helper\CsvExport')
            ->setConstructorArgs([$this->view])
            ->setMethods(['sendHeaders'])
            ->getMock();
    }

    public function testGetInstanceFromView()
    {
        $this->assertInstanceOf('\Dewdrop\View\Helper\CsvExport', $this->view->csvExport());
    }

    public function testUsage()
    {
        $output = $this->csvExportViewHelper->render($this->component);

        $this->assertInternalType('string', $output);
    }
}

abstract class TestAdminModel extends Table implements AdminModelInterface {};

