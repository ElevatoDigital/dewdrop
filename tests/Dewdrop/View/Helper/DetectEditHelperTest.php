<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Test\BaseTestCase;
use Dewdrop\View\View;

class DetectEditHelperTest extends BaseTestCase
{
    private $detector;

    public function setUp()
    {
        $this->detector = $this->getMock(
            '\Dewdrop\Fields\EditHelperDetector',
            array('detect', 'customizeField'),
            array()
        );
    }

    public function testCustomizeMethodCallsRelatedMethodOnDetectorObject()
    {
        $view = new View();

        $this->detector
            ->expects($this->once())
            ->method('customizeField')
            ->with('model:field_name', 'wpSelect');

        $view->detectEditHelper()
            ->setDetector($this->detector)
            ->customize('model:field_name', 'wpSelect');
    }

    public function testRenderMethodCallsDetectorAndRendersHelper()
    {
        $view = $this->getMock(
            '\Dewdrop\View\View',
            array('wpInputText')
        );

        $this->detector
            ->expects($this->once())
            ->method('detect')
            ->will($this->returnValue('wpInputText'));

        $view->detectEditHelper()->setDetector($this->detector);

        $view
            ->expects($this->once())
            ->method('wpInputText');

        $view->detectEditHelper()->render($this->getTestField());
    }

    public function testNotExplicitlySettingDetectorWillCreateOne()
    {
        $view  = new \Dewdrop\View\View();
        $field = $this->getTestField();

        $view->detectEditHelper()->customize($field, 'wpInputCheckbox');

        $output = $view->detectEditHelper()->render($field);

        $this->assertMatchesDomQuery('input[type="checkbox"]', $output);
    }

    private function getTestField()
    {
        $db = new \Dewdrop\Db\Adapter\Mock();

        require_once __DIR__ . '/table/DewdropTestFruits.php';
        $table = new \DewdropViewHelperTest\DewdropTestFruits($db);
        $row   = $table->createRow();
        $field = $row->field('name');

        return $field;
    }
}
