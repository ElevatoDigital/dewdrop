<?php

namespace Dewdrop\View\Helper;

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

        $this->csvExportViewHelper = $this->getMockBuilder('\Dewdrop\View\Helper\CsvExport')
            ->setConstructorArgs([$this->view])
            ->setMethods(['sendHeaders'])
            ->getMock();
    }

    public function testGetInstanceFromView()
    {
        $this->assertInstanceOf('\Dewdrop\View\Helper\CsvExport', $this->view->csvExport());
    }

    public function testBasicUsageRendersCsv()
    {
        $fields = new Fields();

        $fields
            ->add('front')
                ->setVisible(true)
                ->setLabel('Front')
                ->assignHelperCallback(
                    'CsvCell.Content',
                    function ($helper, $rowData) {
                        return $rowData['test_field'];
                    }
                )
            ->add('back')
                ->setVisible(true)
                ->setLabel('Back')
                ->assignHelperCallback(
                    'CsvCell.Content',
                    function ($helper, $rowData) {
                        return strrev($rowData['test_field']);
                    }
                );

        $output = $this->csvExportViewHelper->direct($fields, array(array('test_field' => 'FAFAFAFA')));

        $this->assertContains('Front,Back', $output);
        $this->assertContains('FAFAFAFA,AFAFAFAF', $output);
    }

    public function testNonVisibleFieldsAreNotRendered()
    {
        $fields = new Fields();

        $fields
            ->add('visible')
                ->setVisible(true)
                ->setLabel('Visible')
                ->assignHelperCallback(
                    'CsvCell.Content',
                    function ($helper, $rowData) {
                        return 'content.visible';
                    }
                )
            ->add('not_visible')
                ->setVisible(false)
                ->setLabel('Unseen')
                ->assignHelperCallback(
                    'CsvCell.Content',
                    function ($helper, $rowData) {
                        return 'content.unseen';
                    }
                );

        $output = $this->csvExportViewHelper->direct($fields, array(array('test_field' => 'FAFAFAFA')));

        $this->assertContains('Visible', $output);
        $this->assertContains('content.visible', $output);
        $this->assertNotContains('Unseen', $output);
        $this->assertNotContains('content.unseen', $output);
    }

    public function testCanOptionallySupplyCustomRenderer()
    {
        $fields = new Fields();

        $fields
            ->add('custom')
                ->setVisible(true)
                ->setLabel('Custom')
                ->assignHelperCallback(
                    'CsvCell.Content',
                    function ($helper, $rowData) {
                        return 'shouldnotberendered';
                    }
                )
            ->add('not_visible')
                ->setVisible(false)
                ->setLabel('Unseen')
                ->assignHelperCallback(
                    'CsvCell.Content',
                    function ($helper, $rowData) {
                        return 'content.unseen';
                    }
                );

        $renderer = $this->view->csvCellRenderer();

        $renderer->getContentRenderer()->assign(
            'custom',
            function ($helper, $rowData) {
                return 'customrendering';
            }
        );

        $output = $this->csvExportViewHelper->direct($fields, array(array('test_field' => 'FAFAFAFA')), $renderer);

        $this->assertContains('customrendering', $output);
        $this->assertNotContains('shouldnotberenderered', $output);
    }

    public function testPassingNoArgumentsToDirectReturnsHelperInstance()
    {
        $this->assertInstanceOf('\Dewdrop\View\Helper\CsvExport', $this->csvExportViewHelper->direct());
    }
}

