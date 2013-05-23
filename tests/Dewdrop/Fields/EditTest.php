<?php

namespace Dewdrop\Fields;

use Dewdrop\Db\Adapter\Mock as MockAdapter;
use Dewdrop\Exception;
use Dewdrop\Test\BaseTestCase;
use Zend\InputFilter\InputFilter;

class EditTest extends BaseTestCase
{
    private $fields;

    private $row;

    private $inputFilter;

    public function setUp()
    {
        require_once dirname(__DIR__) . '/Db/table/DewdropTestFruits.php';

        $this->inputFilter = new InputFilter();

        $db    = new MockAdapter();
        $table = new \DewdropTest\DewdropTestFruits($db);

        $this->fields = new Edit($this->inputFilter);
        $this->row    = $table->createRow();
    }

    public function testAddingFieldAlsoAddsToInputFilter()
    {
        $this->assertEquals(0, $this->inputFilter->count());
        $this->fields->add($this->row->field('name'));
        $this->assertEquals(1, $this->inputFilter->count());
    }

    public function testAddingFieldWithGroupNameChangesControNameOnFieldItself()
    {
        $this->fields->add($this->row->field('name'), 'group');

        $this->assertEquals('group:name', $this->row->field('name')->getControlName());
        $this->assertInstanceOf('\Dewdrop\Db\Field', $this->fields->get('group:name'));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testRetrievingUnknownFieldThrowsException()
    {
        $this->fields->get('fafafafa');
    }

    public function testHasMethodReturnsTrueForKnownField()
    {
        $this->fields->add($this->row->field('name'));

        $this->assertTrue($this->fields->has('dewdrop_test_fruits:name'));
    }

    public function testHasMethodReturnsFalseForUnknownField()
    {
        $this->assertFalse($this->fields->has('fafafafa'));
    }

    public function testSetValuesModifiesRelatedRowObject()
    {
        $this->fields
            ->add($this->row->field('name'))
            ->add($this->row->field('is_delicious'));

        $this->fields->setValues(
            array(
                'dewdrop_test_fruits:name'         => 'fafafafa',
                'dewdrop_test_fruits:is_delicious' => 1
            )
        );

        $this->assertEquals('fafafafa', $this->row->get('name'));
        $this->assertEquals(1, $this->row->get('is_delicious'));
    }

    public function testBooleanMissingFromSetValuesIsSetToFalse()
    {
        $this->row->set('is_delicious', 1);

        $this->fields
            ->add($this->row->field('is_delicious'));

        $this->fields->setValues(
            array(
                'unknown_field' => 'test'
            )
        );

        $this->assertEquals(0, $this->row->get('is_delicious'));
    }

    public function testCanIterateOverFieldCollection()
    {
        $this->fields
            ->add($this->row->field('name'))
            ->add($this->row->field('is_delicious'));

        $count = 0;

        foreach ($this->fields as $field) {
            $count += 1;
        }

        $this->assertEquals(2, $count);
    }
}
