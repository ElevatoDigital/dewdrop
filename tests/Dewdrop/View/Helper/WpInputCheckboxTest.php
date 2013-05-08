<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Field;
use Dewdrop\Test\BaseTestCase;
use Dewdrop\View\View;

class WpInputCheckboxTest extends BaseTestCase
{
    private $view;

    public function setUp()
    {
        $this->view = new View();
    }

    public function testCanRenderUsingExplictArguments()
    {
        $out = $this->view->wpInputCheckbox('fafafafa', false, 'Just a checkbox');

        $this->assertMatchesDomQuery('input[type="checkbox"][name="fafafafa"][id="fafafafa"]', $out);
        $this->assertMatchesDomQuery('label[for="fafafafa"]', $out);
        $this->assertContains('Just a checkbox', $out);
        $this->assertEquals(0, count($this->queryDom('input[checked="checked"]', $out)));
    }

    public function testWillEscapeLabelContent()
    {
        $out = $this->view->wpInputCheckbox('fafafafa', false, 'Escaped & Safe');

        $this->assertContains('&amp;', $out);
    }

    public function testWillEscapeIdAttribute()
    {
        $out = $this->view->wpInputCheckbox(
            array(
                'id'    => 'fafa&fafa',
                'value' => false,
                'label' => 'Label',
                'name'  => 'fafafafa'
            )
        );

        $this->assertContains('&amp;', $out);
    }

    public function testWillEscapeNameAttribute()
    {
        $out = $this->view->wpInputCheckbox(
            array(
                'name'  => 'fafa&fafa',
                'value' => false,
                'label' => 'Label',
                'id'    => 'fafafafa'
            )
        );

        $this->assertContains('&amp;', $out);
    }

    public function testWillRenderCheckedAttribute()
    {
        $out = $this->view->wpInputCheckbox('fafafafa', true, 'This is Checked');

        $this->assertMatchesDomQuery('input[checked="checked"]', $out);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLackOfNameArgumentWillThrowException()
    {
        $this->view->wpInputCheckbox(
            array(
                'value' => false
            )
        );
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLackOfValueArgumentWillThrowException()
    {
        $this->view->wpInputCheckbox(
            array(
                'name' => 'fafafafa'
            )
        );
    }

    public function testCanRenderCheckboxUsingADbField()
    {
        $db = new \Dewdrop\Db\Adapter\Mock();

        require_once __DIR__ . '/table/DewdropTestFruits.php';
        $table = new \DewdropViewHelperTest\DewdropTestFruits($db);
        $row   = $table->createRow();
        $field = $row->field('is_delicious');

        $out = $this->view->wpInputCheckbox($field);

        $this->assertMatchesDomQuery(
            'input[name="' . $field->getControlName() . '"]',
            $out
        );

        $this->assertMatchesDomQuery(
            'input[id="' . $field->getHtmlId() . '"]',
            $out
        );

        $this->assertContains($field->getLabel(), $out);
    }
}
