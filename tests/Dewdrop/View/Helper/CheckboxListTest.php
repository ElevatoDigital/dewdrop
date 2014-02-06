<?php

namespace Dewdrop\View\Helper;

use Dewdrop\View\View;
use Dewdrop\Test\BaseTestCase;

class CheckboxListTest extends BaseTestCase
{
    private $view;

    public function setUp()
    {
        $this->view = new View();
    }

    public function testWillRenderListItemsForEachOption()
    {
        $out = $this->view->checkboxList(
            array(
                'name' => 'fafafafa',
                'options' => array(
                    1 => 'First',
                    2 => 'Second'
                ),
                'value' => null
            )
        );

        $this->assertEquals(2, count($this->queryDom('li', $out)));
    }

    public function testItemsInTheValuesArrayAreChecked()
    {
        $out = $this->view->checkboxList(
            array(
                'name' => 'fafafafa',
                'options' => array(
                    1 => 'First',
                    2 => 'Second'
                ),
                'value' => array(1)
            )
        );

        $this->assertMatchesDomQuery('input[type="checkbox"][value="1"][checked="checked"]', $out);
        $this->assertEquals(0, count($this->queryDom('input[type="checkbox"][value="2"][checked="checked"]', $out)));
    }

    public function testCanRenderWithExplicitlyPassedArguments()
    {
        $out = $this->view->checkboxList(
            'fafafafa',
            array(
                1 => 'First',
                2 => 'Second'
            ),
            array(1)
        );

        $this->assertMatchesDomQuery('input[name="fafafafa[]"]', $out);
        $this->assertMatchesDomQuery('input[id="fafafafa_1"]', $out);
        $this->assertMatchesDomQuery('input[id="fafafafa_2"]', $out);
        $this->assertMatchesDomQuery('input[value="1"][checked="checked"]', $out);
    }
}
