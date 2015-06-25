<?php

namespace Dewdrop\Fields\Listing;

use Dewdrop\Db\Adapter\Mock as MockAdapter;
use Dewdrop\Fields;
use Dewdrop\Fields\Listing;
use Dewdrop\Fields\Listing\BulkActions;
use Dewdrop\Request;

class BulkActionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BulkActions
     */
    private $bulkActions;

    private $fields;

    private $model;

    private $listing;

    public function setUp()
    {
        require_once __DIR__ . '/../test-tables/DewdropTestFruits.php';

        $db = new MockAdapter();

        $this->fields  = new Fields();
        $this->model   = new \DewdropFieldsTest\DewdropTestFruits($db);
        $this->listing = new Listing($this->model->select(), $this->model->field('dewdrop_test_fruit_id'));

        $this->bulkActions = new BulkActions($this->listing, $this->fields);
    }

    public function testCanAddButtonActionWithShortcutMethod()
    {
        $this->bulkActions->addButton(
            'my_button',
            'My Title',
            function (array $selected) {
                return $selected;
            }
        );

        $this->assertEquals(1, count($this->bulkActions->getActions()));

        $button = current($this->bulkActions->getActions());

        $this->assertInstanceOf('\Dewdrop\Fields\Listing\BulkActions\Button', $button);
    }

    public function testEmptyRequestDataResultsInActionNotBeingProcessed()
    {
        $request     = new Request();
        $listing     = new Listing($this->model->select(), $this->model->field('dewdrop_test_fruit_id'), $request);
        $bulkActions = new BulkActions($listing, $this->fields);

        $action = $this->getMock(
            '\Dewdrop\Fields\Listing\BulkActions\ActionInterface',
            array('shouldProcess', 'process', 'render'),
            array($bulkActions)
        );

        $bulkActions->add($action);

        $action->expects($this->never())
            ->method('shouldProcess')
            ->will($this->returnValue(true));

        $action->expects($this->never())
            ->method('process');

        $bulkActions->process();
    }

    public function testRequestDataIsPassedAlongToAction()
    {
        $request     = new Request(['bulk_selections' => [1, 2, 3]]);
        $listing     = $this->createListingWithRequest($request);
        $bulkActions = new BulkActions($listing, $this->fields);

        $action = $this->getMock(
            '\Dewdrop\Fields\Listing\BulkActions\ActionInterface',
            array('shouldProcess', 'process', 'render'),
            array($bulkActions)
        );

        $bulkActions->add($action);

        $action->expects($this->once())
            ->method('shouldProcess')
            ->will($this->returnValue(true));

        $action->expects($this->once())
            ->method('process')
            ->with([1, 2, 3]);

        $bulkActions->process();
    }

    public function testRequestDataWithInvalidListingIdsIsIgnored()
    {
        $request     = new Request(['bulk_selections' => [1, 2, 3, 12, 13, 14]]);
        $listing     = $this->createListingWithRequest($request);
        $bulkActions = new BulkActions($listing, $this->fields);

        $action = $this->getMock(
            '\Dewdrop\Fields\Listing\BulkActions\ActionInterface',
            array('shouldProcess', 'process', 'render'),
            array($bulkActions)
        );

        $bulkActions->add($action);

        $action->expects($this->once())
            ->method('shouldProcess')
            ->will($this->returnValue(true));

        $action->expects($this->once())
            ->method('process')
            ->with([1, 2, 3]);

        $bulkActions->process();
    }

    public function testCheckPagesInRequestDataIncludesAllListingIds()
    {
        $request     = new Request(['bulk_selections' => [], 'bulk_selections_check_pages' => 1]);
        $listing     = $this->createListingWithRequest($request);
        $bulkActions = new BulkActions($listing, $this->fields);

        $action = $this->getMock(
            '\Dewdrop\Fields\Listing\BulkActions\ActionInterface',
            array('shouldProcess', 'process', 'render'),
            array($bulkActions)
        );

        $bulkActions->add($action);

        $action->expects($this->once())
            ->method('shouldProcess')
            ->will($this->returnValue(true));

        $action->expects($this->once())
            ->method('process')
            ->with([1, 2, 3, 4, 5]);

        $bulkActions->process();
    }

    public function testCanGetSelectedOptionsAfterProcessIsCalled()
    {
        $request     = new Request(['bulk_selections' => [], 'bulk_selections_check_pages' => 1]);
        $listing     = $this->createListingWithRequest($request);
        $bulkActions = new BulkActions($listing, $this->fields);

        $bulkActions->process();

        $this->assertEquals([1, 2, 3, 4, 5], $bulkActions->getSelected());
    }

    public function testCanAccessListingPrimaryKeyFieldViaBulkActions()
    {
        $request     = new Request(['bulk_selections' => [], 'bulk_selections_check_pages' => 1]);
        $listing     = $this->createListingWithRequest($request);
        $bulkActions = new BulkActions($listing, $this->fields);

        $this->assertEquals($this->model->field('dewdrop_test_fruit_id'), $bulkActions->getPrimaryKey());
    }

    public function testCanUseCustomInputId()
    {
        $request     = new Request(['fafafafa' => [], 'fafafafa_check_pages' => 1]);
        $listing     = $this->createListingWithRequest($request);
        $bulkActions = new BulkActions($listing, $this->fields);

        $bulkActions->setId('fafafafa');
        $this->assertEquals('fafafafa', $bulkActions->getId());

        $action = $this->getMock(
            '\Dewdrop\Fields\Listing\BulkActions\ActionInterface',
            array('shouldProcess', 'process', 'render'),
            array($bulkActions)
        );

        $bulkActions->add($action);

        $action->expects($this->once())
            ->method('shouldProcess')
            ->will($this->returnValue(true));

        $action->expects($this->once())
            ->method('process')
            ->with([1, 2, 3, 4, 5]);

        $bulkActions->process();
    }

    private function createListingWithRequest(Request $request)
    {
        $listing = $this->getMock(
            '\Dewdrop\Fields\Listing',
            ['fetchData'],
            [$this->model->select(), $this->model->field('dewdrop_test_fruit_id'), $request]
        );

        $listing->expects($this->any())
            ->method('fetchData')
            ->will($this->returnValue(
                [
                    ['dewdrop_test_fruit_id' => 1],
                    ['dewdrop_test_fruit_id' => 2],
                    ['dewdrop_test_fruit_id' => 3],
                    ['dewdrop_test_fruit_id' => 4],
                    ['dewdrop_test_fruit_id' => 5]
                ]
            ));

        return $listing;
    }
}
