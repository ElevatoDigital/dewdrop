<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Test\BaseTestCase;
use Dewdrop\View\View;

class WpEditorTest extends BaseTestCase
{
    private $view;

    public function setUp()
    {
        if (!defined('WPINC')) {
            $this->markTestSkipped('Not running in WP plugin context');
        }

        $this->view = new View();
    }

    public function testCanRenderUsingExplicitArguments()
    {
        $out = $this->view->wpEditor('content', 'This is the value');

        $this->assertMatchesDomQuery('textarea[name="content"][id="content"]', $out);
        $this->assertMatchesDomQuery('#editor-buttons-css', $out);
        $this->assertContains('This is the value', $out);
    }

    public function testCanRenderUsingArrayArguments()
    {
        $out = $this->view->wpEditor(
            array(
                'name'  => 'content',
                'value' => 'This is the value',
                'id'    => 'id_string'
            )
        );

        $this->assertMatchesDomQuery('textarea[name="content"]', $out);
        $this->assertMatchesDomQuery('textarea[id="id_string"]', $out);
        $this->assertContains('This is the value', $out);
    }

    public function testCanRenderUseFieldObject()
    {
        $db = new \Dewdrop\Db\Adapter\Mock();

        require_once __DIR__ . '/table/DewdropTestFruits.php';
        $table = new \DewdropViewHelperTest\DewdropTestFruits($db);
        $row   = $table->createRow();
        $field = $row->field('name');

        $out = $this->view->wpEditor($field);

        $this->assertMatchesDomQuery('textarea[name="dewdrop_test_fruits:name"]', $out);
        $this->assertMatchesDomQuery('textarea[id="dewdrop_test_fruits_name"]', $out);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLackOfNameArgumentThrowsException()
    {
        $this->view->wpEditor(
            array(
                'value' => null
            )
        );
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLackOfValueArgumentThrowsException()
    {
        $this->view->wpEditor(
            array(
                'name' => 'test'
            )
        );
    }
}
