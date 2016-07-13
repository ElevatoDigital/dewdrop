<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Fields;
use Dewdrop\Test\BaseTestCase;
use Dewdrop\View\View;
use PHPUnit_Framework_TestCase;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;

class BootstrapFormTest extends BaseTestCase
{
    private $view;

    private $inputFilter;

    public function setUp()
    {
        $this->view = new View();
    }

    public function testCallingDirectlyWithNoArgsReturnsHelperInstance()
    {
        $this->assertInstanceOf('Dewdrop\View\Helper\BootstrapForm', $this->view->bootstrapForm());
    }

    public function testOpenMethodRendersAFormTagWithNoActionAndPostByDefault()
    {
        $html = $this->view->bootstrapForm()->open();

        $this->assertContains('<form ', $html);
        $this->assertContains('method="POST"', $html);
        $this->assertContains('action=""', $html);
        $this->assertContains('id=""', $html);
        $this->assertContains('class=""', $html);
    }

    public function testOpenMethodAllowsCustomAction()
    {
        $this->assertContains('action="CUSTOM"', $this->view->bootstrapForm()->open('CUSTOM'));
    }

    public function testOpenMethodAllowsCustomMethod()
    {
        $this->assertContains('method="CUSTOM"', $this->view->bootstrapForm()->open('', 'CUSTOM'));
    }

    public function testOpenMethodAllowsCustomId()
    {
        $this->assertContains('id="CUSTOM"', $this->view->bootstrapForm()->open('', '', 'CUSTOM'));
    }

    public function testOpenMethodAllowsCustomClass()
    {
        $this->assertContains('class="CUSTOM"', $this->view->bootstrapForm()->open('', '', '', 'CUSTOM'));
    }

    public function testOpenMethodAllowsMultipleCustomClassesWithHtmlEscaped()
    {
        $this->assertContains('class="CUSTOM&#x20;CUSTOM2"', $this->view->bootstrapForm()->open('', '', '', 'CUSTOM CUSTOM2'));
    }

    public function testCloseMethodClosesTheFormTag()
    {
        $this->assertEquals('</form>', $this->view->bootstrapForm()->close());
    }

    public function testRenderSubmitButtonAllowsCustomClass()
    {
        $this->assertContains('class="CUSTOM', $this->view->bootstrapForm()->renderSubmitButton('', 'CUSTOM'));
    }

    public function testRenderSubmitButtonAllowsMultipleCustomClassesWithHtmlEscaped()
    {
        $this->assertContains('class="CUSTOM&#x20;CUSTOM2', $this->view->bootstrapForm()->renderSubmitButton('', 'CUSTOM CUSTOM2'));
    }

    public function testRenderFieldsExcludesNonEditableFields()
    {
        $html = $this->view->bootstrapForm()->renderFields(
            $this->getFields(),
            new InputFilter(),
            $this->view->editControlRenderer()
        );

        $this->assertContains('One', $html);
        $this->assertContains('Two', $html);
        $this->assertNotContains('Three', $html);
    }

    public function testRenderFieldsShowsRequiredFlagForAppropriateFields()
    {
        $inputFilter = new InputFilter();

        $input = new Input('one');
        $input->setRequired(true);

        $inputFilter->add($input);

        $html = $this->view->bootstrapForm()->renderFields(
            $this->getFields(),
            $inputFilter,
            $this->view->editControlRenderer()
        );

        $this->assertEquals(1, count($this->queryDom('.glyphicon-asterisk', $html)));
    }

    private function getFields()
    {
        $fields = new Fields();

        $fields
            ->add('one')
                ->setEditable(true)
                ->setLabel('One')
                ->assignHelperCallback(
                    'EditControl.Control',
                    function ($helper, $view) {
                        return 'FIELD_ONE';
                    }
                )
            ->add('two')
                ->setEditable(true)
                ->setLabel('Two')
                ->assignHelperCallback(
                    'EditControl.Control',
                    function ($helper, $view) {
                        return 'FIELD_TWO';
                    }
                )
            ->add('three')
                ->setEditable(false)
                ->setLabel('Not Editable');

        return $fields;
    }
}
