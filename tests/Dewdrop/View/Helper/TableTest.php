<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Test\BaseTestCase;
use Dewdrop\View\View;
use Dewdrop\Fields;
use Dewdrop\Fields\Field;

class TableTest extends BaseTestCase
{
    private $view;

    public function setUp()
    {
        $this->view = new View();
        $this->fields = new Fields();
        $this->data = array(array('id' => 2, 'name' => 'Apple'));
        $_SERVER['REQUEST_URI'] = null;

        $field = new Field();

        $field
            ->setId('test-field-id')
            ->setVisible(true)
            ->setSortable(true)
            ->assignHelperCallback(
                'TableCell.Content',
                function ($helper, $view) {
                    return 'test-field-value';
                }
            )
            ->assignHelperCallback(
                'tablecell.tdclassnames',
                function ($helper, $view) {
                    return ['test-field-class'];
                }
            );
        $this->fields->add($field);
    }

    public function testOmittingAllArgumentsReturnsSelf()
    {
        $out = $this->view->table();
        $this->assertInstanceOf('\Dewdrop\View\Helper\Table', $out);
    }

    public function testTableHasTableStructure()
    {
        $out = $this->view->table($this->fields, array());

        $this->assertMatchesDomQuery('table', $out);
        $this->assertMatchesDomQuery('table thead', $out);
        $this->assertMatchesDomQuery('table tbody', $out);
    }

    public function testFieldAndRowClasses()
    {
        $renderer = $this->view->tableCellRenderer();
        $renderer->setRowClassCallback(
            function (array $row) {
                return ['test-row-class'];
            }
        );

        $out = $this->view->table($this->fields, $this->data, $renderer);

        $this->assertMatchesDomQuery('table tbody tr.test-row-class', $out);
        $this->assertMatchesDomQuery('table tbody tr td.test-field-class', $out);
    }

    public function testFieldValue()
    {
        $out = $this->view->table($this->fields, $this->data);

        $result = $this->queryDom('table tbody tr td', $out);
        $out = $result->current()->textContent;

        $this->assertEquals('test-field-value', $out);
    }

    /**
     * @group b0rked
     */
    public function testSorting()
    {
        $fieldSortedOn = $this->fields->get('test-field-id');

        /* test default ascending th link */
        $out = $this->view->table($this->fields, array());

        $result = $this->queryDom('table thead th a', $out);
        $out = $result->current()->getAttribute('href');

        $this->assertEquals('?sort=test-field-id&dir=asc', $out);

        /* test default descending th link, given a query param */
        $request = $this->view->getRequest();
        $request->setQuery('sort', 'test-field-id')
            ->setQuery('dir', 'asc');
        $sorter = $this->getMock('\Dewdrop\Fields\Helper\SelectSort',
            ['isSorted', 'getSortedField', 'getSortedDirection'],
            [$request]
        );
        $sorter->expects($this->any())
            ->method('isSorted')
            ->will($this->returnValue(true));

        $sorter->expects($this->any())
            ->method('getSortedField')
            ->will($this->returnValue($fieldSortedOn));

        $sorter->expects($this->any())
            ->method('getSortedDirection')
            ->will($this->returnValue('ASC'));

        $out = $this->view->table($this->fields, array(), null, $sorter);

        $result = $this->queryDom('table thead th a', $out);
        $out = $result->current()->getAttribute('href');

        $this->assertEquals('?sort=test-field-id&dir=desc', $out);

        /* test that sort link is not there when sort is not set */
        $fieldSortedOn->setSortable(false);

        $out = $this->view->table($this->fields, array());
        $this->assertNotMatchesDomQuery('table thead th a', $out);
    }
}
