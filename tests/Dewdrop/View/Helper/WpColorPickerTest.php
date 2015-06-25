<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Test\BaseTestCase;
use Dewdrop\View\View;

class WpColorPickerTest extends BaseTestCase
{
    private $view;

    public function setUp()
    {
        if (!defined('WPINC')) {
            $this->markTestSkipped('Not running in WP plugin context');
        }

        $this->view = new View();
    }

    public function testCanRenderWithArrayOfArguments()
    {
        $out = $this->view->wpColorPicker(
            array(
                'name'  => 'color_picker_name',
                'id'    => 'color_picker_id',
                'value' => '#ff0000'
            )
        );

        $this->assertMatchesDomQuery('input[name="color_picker_name"]', $out);
        $this->assertMatchesDomQuery('input[id="color_picker_id"]', $out);
        $this->assertContains('ff0000', $out);
    }

    public function testRendersATextInputForProgressiveEnhancement()
    {
        $view = $this->getMock(
            '\Dewdrop\View\View',
            array('wpInputText'),
            array()
        );

        $view
            ->expects($this->once())
            ->method('wpInputText');

        $view->wpColorPicker(
            array(
                'defaultColor' => '#ffffff',
                'palettes'     => array('#ff0000', '#00ff00', '#0000ff'),
                'name'         => 'color_picker_name',
                'id'           => 'color_picker_id',
                'value'        => null
            )
        );
    }

    public function testInlineScriptIsQueuedWithExpectedOptions()
    {
        $view = $this->getMock(
            '\Dewdrop\View\View',
            array('inlineScript'),
            array()
        );

        $view
            ->expects($this->once())
            ->method('inlineScript')
            ->with(
                'wp-color-picker.js',
                array(
                    'defaultColor' => '#ffffff',
                    'palettes'     => array('#ff0000', '#00ff00', '#0000ff'),
                    'id'           => 'color_picker_id',
                )
            );

        $view->wpColorPicker(
            array(
                'defaultColor' => '#ffffff',
                'palettes'     => array('#ff0000', '#00ff00', '#0000ff'),
                'name'         => 'color_picker_name',
                'id'           => 'color_picker_id',
                'value'        => null
            )
        );
    }

    public function testWpColorPickerStylesAndScriptsAreEnqueued()
    {
        global $wp_scripts;
        global $wp_styles;

        $wp_scripts = $this->getMock(
            '\WP_Scripts',
            array('enqueue')
        );

        $wp_scripts
            ->expects($this->once())
            ->method('enqueue')
            ->with('wp-color-picker');

        $wp_styles = $this->getMock(
            '\WP_Styles',
            array('enqueue')
        );

        $wp_styles
            ->expects($this->once())
            ->method('enqueue')
            ->with('wp-color-picker');

        $this->view->wpColorPicker(
            array(
                'name'  => 'color_picker_name',
                'id'    => 'color_picker_id',
                'value' => '#ff0000'
            )
        );
    }

    public function testCanRenderByPassingArgumentsExplicitly()
    {
        $out = $this->view->wpColorPicker('color_picker', '#ff0000');

        $this->assertMatchesDomQuery('input[name="color_picker"]', $out);
        $this->assertMatchesDomQuery('input[id="color_picker"]', $out);
        $this->assertContains('ff0000', $out);
    }

    public function testCanRenderUsingADbFieldArgument()
    {
        $db = new \Dewdrop\Db\Adapter\Mock();

        require_once __DIR__ . '/table/DewdropTestFruits.php';
        $model = new \DewdropViewHelperTest\DewdropTestFruits($db);
        $row   = $model->createRow();

        $row->set('name', '#ff0000');

        $out = $this->view->wpColorPicker($row->field('name'));

        $this->assertMatchesDomQuery('input[name="dewdrop_test_fruits:name"]', $out);
        $this->assertMatchesDomQuery('input[id="dewdrop_test_fruits_name"]', $out);
        $this->assertContains('ff0000', $out);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLeavingOutNameArgumentThrowsException()
    {
        $out = $this->view->wpColorPicker(
            array(
                'value' => null
            )
        );
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLeavingOutValueArgumentThrowsException()
    {
        $out = $this->view->wpColorPicker(
            array(
                'name' => 'test'
            )
        );
    }
}
