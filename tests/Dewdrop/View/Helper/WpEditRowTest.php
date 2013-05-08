<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Test\BaseTestCase;
use Dewdrop\View\View;

class WpEditRowTest extends BaseTestCase
{
    private $view;

    public function setUp()
    {
        $this->view = new View();
    }

    public function testCanRenderOpenWithExplicitArgsAndNoLabelForAttribute()
    {
        $out = $this->view->wpEditRow()->open('Label Content Here');

        $this->assertEquals(0 , count($this->queryDom('label', $out)));
        $this->assertContains('Label Content Here', $out);
    }

    public function testCanRenderOpenWithExplicitArgsAndLabelForAttribute()
    {
        $out = $this->view->wpEditRow()->open('Label Content Here', 'for_id');

        $this->assertMatchesDomQuery('label[for="for_id"]', $out);
        $this->assertContains('Label Content Here', $out);
    }

    public function testCanRenderOpenWithWithArrayArgs()
    {
        $out = $this->view->wpEditRow()->open(
            array(
                'label'    => 'Label Content Here',
                'labelFor' => 'for_id'
            )
        );

        $this->assertMatchesDomQuery('label[for="for_id"]', $out);
        $this->assertContains('Label Content Here', $out);
    }

    public function testCanRenderOpenWithWithFieldObject()
    {
        $db = new \Dewdrop\Db\Adapter\Mock();

        require_once __DIR__ . '/table/DewdropTestFruits.php';
        $table = new \DewdropViewHelperTest\DewdropTestFruits($db);
        $row   = $table->createRow();
        $field = $row->field('name');

        $out = $this->view->wpEditRow()->open($field);

        $this->assertMatchesDomQuery('label[for="dewdrop_test_fruits_name"]', $out);
        $this->assertContains('Name', $out);
    }

    public function testCloseMethodRendersClosingRowAndCellTags()
    {
        $out = $this->view->wpEditRow()->close();

        $this->assertContains('</td>', $out);
        $this->assertContains('</tr>', $out);
    }
}
