<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Field;
use Dewdrop\Test\BaseTestCase;
use Dewdrop\View\View;

class TextareaTest extends BaseTestCase
{
    private $view;

    public function setUp()
    {
        $this->view = new View();
    }

    public function testCanRenderUsingExplictArguments()
    {
        $out = $this->view->textarea(
            'fafafafa',
            'Just a Test',
            'textarea-class'
        );

        $this->assertMatchesDomQuery('textarea[name="fafafafa"]', $out);
        $this->assertMatchesDomQuery('textarea[id="fafafafa"]', $out);
        $this->assertMatchesDomQuery('textarea[class*="textarea-class"]', $out);
        $this->assertContains('Just a Test', $out);
    }

    public function testWillEscapeIdAttribute()
    {
        $out = $this->view->textarea(
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
        $out = $this->view->textarea(
            array(
                'name'  => 'fafa&fafa',
                'value' => false,
                'label' => 'Label',
                'id'    => 'fafafafa'
            )
        );

        $this->assertContains('&amp;', $out);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLackOfNameArgumentWillThrowException()
    {
        $this->view->textarea(
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
        $this->view->textarea(
            array(
                'name' => 'fafafafa'
            )
        );
    }

    public function testCanRenderTextareaUsingADbField()
    {
        $db = new \Dewdrop\Db\Adapter\Mock();

        require_once __DIR__ . '/table/DewdropTestFruits.php';
        $table = new \DewdropViewHelperTest\DewdropTestFruits($db);
        $row   = $table->createRow();
        $field = $row->field('name');

        $out = $this->view->textarea($field);

        $this->assertMatchesDomQuery(
            'textarea[name="' . $field->getControlName() . '"]',
            $out
        );

        $this->assertMatchesDomQuery(
            'textarea[id="' . $field->getHtmlId() . '"]',
            $out
        );
    }
}
