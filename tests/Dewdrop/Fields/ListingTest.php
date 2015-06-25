<?php

namespace Dewdrop\Fields;

use Dewdrop\Db\Table;
use Dewdrop\Fields;
use Dewdrop\Pimple;
use Dewdrop\Request;

class ListingFruitsModel extends Table
{
    public function init()
    {
        $this->setTableName('dewdrop_test_fruits');
    }

    public function selectListing()
    {
        return $this->select()
            ->from('dewdrop_test_fruits');
    }
}

class ListingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ListingFruitsModel
     */
    private $model;

    /**
     * @var Listing
     */
    private $listing;

    public function setUp()
    {
        $this->model = new ListingFruitsModel();

        $this->listing = new Listing(
            $this->model->selectListing(),
            $this->model->field('dewdrop_test_fruit_id')
        );
    }

    public function testListingWillUsePimplesRequestObjectIfNoneIsProvided()
    {
        $this->assertEquals(
            spl_object_hash(Pimple::getResource('dewdrop-request')),
            spl_object_hash($this->listing->getRequest())
        );
    }

    public function testListingWillUseManuallyProvidedRequestObject()
    {
        $request = new Request();

        $listing = new Listing(
            $this->model->selectListing(),
            $this->model->field('dewdrop_test_fruit_id'),
            $request
        );

        $this->assertEquals(
            spl_object_hash($request),
            spl_object_hash($listing->getRequest())
        );

        $this->assertNotEquals(
            spl_object_hash(Pimple::getResource('dewdrop-request')),
            spl_object_hash($listing->getRequest())
        );
    }

    public function testListingHasASelectFilterModifier()
    {
        $this->assertInstanceOf(
            '\Dewdrop\Fields\Helper\SelectFilter',
            $this->listing->getSelectModifierByName('selectfilter')
        );
    }

    public function testListingHasASelectSortModifier()
    {
        $this->assertInstanceOf(
            '\Dewdrop\Fields\Helper\SelectSort',
            $this->listing->getSelectModifierByName('selectsort')
        );
    }

    public function testListingHasASelectPaginateModifier()
    {
        $this->assertInstanceOf(
            '\Dewdrop\Fields\Helper\SelectPaginate',
            $this->listing->getSelectModifierByName('selectpaginate')
        );
    }

    public function testGettingUnknownSelectModifierReturnsFalse()
    {
        $this->assertFalse($this->listing->getSelectModifierByName('fafafafa'));
    }

    public function testCanRetrieveAllSelectModifiers()
    {
        $this->assertEquals(3, count($this->listing->getSelectModifiers()));
    }

    public function testCanGetAndSetListingPrefix()
    {
        $this->listing->setPrefix('PREFIX');
        $this->assertEquals('PREFIX', $this->listing->getPrefix());
    }

    public function testSelectModifiersReceiveThePrefixOfTheListing()
    {
        $this->listing->setPrefix('PREFIX');

        $this->assertEquals(
            'PREFIX',
            $this->listing->getSelectModifierByName('selectsort')->getPrefix()
        );

        $this->assertEquals(
            'PREFIX',
            $this->listing->getSelectModifierByName('selectfilter')->getPrefix()
        );

        $this->assertEquals(
            'PREFIX',
            $this->listing->getSelectModifierByName('selectpaginate')->getPrefix()
        );
    }

    public function testCanFetchSingleRowFromListingUsingPrimaryKey()
    {
        $ids = $this->setUpDb();

        $this->assertEquals(
            'APPLE',
            $this->listing->fetchRow($this->getFields(), $ids['APPLE'])['name']
        );

        $this->assertEquals(
            'ORANGE',
            $this->listing->fetchRow($this->getFields(), $ids['ORANGE'])['name']
        );
    }

    public function testCanRetrieveAllData()
    {
        $this->setUpDb();

        $this->assertEquals(2, count($this->listing->fetchData($this->getFields())));
    }

    public function testCanGetTotalRowCountAfterFetchingData()
    {
        $this->setUpDb();

        /* @var $paginateHelper \Dewdrop\Fields\Helper\SelectPaginate */
        $paginateHelper = $this->listing->getSelectModifierByName('selectpaginate');
        $paginateHelper->setPageSize(1);

        $this->assertEquals(1, count($this->listing->fetchData($this->getFields())));
        $this->assertEquals(2, $this->listing->getTotalRowCount());
    }

    public function testFetchDataStillReturnsArrayWhenThereIsNoData()
    {
        $db  = Pimple::getResource('db');

	$db->query('DELETE FROM dewdrop_test_fruits');

        $this->assertEquals(array(), $this->listing->fetchData($this->getFields()));
    }

    public function testGetModifiedSelectMethodReturnsClonedSelectObject()
    {
        $select = $this->model->selectListing();

        $listing = new Listing(
            $this->model->selectListing(),
            $this->model->field('dewdrop_test_fruit_id')
        );

        $this->assertNotEquals(
            spl_object_hash($select),
            spl_object_hash($listing->getModifiedSelect($this->getFields()))
        );
    }

    public function testCanRetrievePrimaryKeyField()
    {
        $this->assertEquals(
            spl_object_hash($this->model->field('dewdrop_test_fruit_id')),
            spl_object_hash($this->listing->getPrimaryKey())
        );
    }

    public function testModifiersCanChangeSelectObjectDuringGetModifiedSelect()
    {
        $modifier = $this->getMock(
            '\Dewdrop\Fields\Helper\SelectModifierInterface',
            array('modifySelect', 'setPrefix', 'getPrefix', 'matchesName'),
            array()
        );

        $select = $this->model->select()
            ->from('dewdrop_test_fruits');

        $modifier->expects($this->any())
            ->method('modifySelect')
            ->will($this->returnValue($select->where('fafafafa')));

        $this->listing->registerSelectModifier($modifier);

        $this->assertContains(
            'fafafafa',
            (string) $this->listing->getModifiedSelect($this->getFields())
        );
    }

    /**
     * @expectedException \Dewdrop\Fields\Exception
     */
    public function testExceptionIsThrownWhenModifierDoesNotReturnASelectObject()
    {
        $modifier = $this->getMock(
            '\Dewdrop\Fields\Helper\SelectModifierInterface',
            array('modifySelect', 'setPrefix', 'getPrefix', 'matchesName'),
            array()
        );

        $select = $this->model->select()
            ->from('dewdrop_test_fruits');

        $modifier->expects($this->any())
            ->method('modifySelect')
            ->will($this->returnValue(null));

        $this->listing->registerSelectModifier($modifier);

        $this->listing->getModifiedSelect($this->getFields());
    }

    private function setUpDb()
    {
        $db  = Pimple::getResource('db');
        $out = array();

	$db->query('DELETE FROM dewdrop_test_fruits');

        $db->insert('dewdrop_test_fruits', array('name' => 'APPLE'));
        $out['APPLE'] = $db->lastInsertId();

        $db->insert('dewdrop_test_fruits', array('name' => 'ORANGE'));
        $out['ORANGE'] = $db->lastInsertId();

        return $out;
    }

    private function getFields()
    {
        $fields = new Fields();

        $fields
            ->add($this->model->field('name'))
            ->add($this->model->field('is_delicious'));

        return $fields;
    }
}
