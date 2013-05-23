<?php

namespace Dewdrop\Fields;

use Dewdrop\Test\BaseTestCase;

class SortedSetTest extends BaseTestCase
{
    private $fields;

    private $set;

    public function setUp()
    {
        $db = new \Dewdrop\Db\Adapter\Mock();

        $inputFilter = new \Zend\InputFilter\InputFilter();

        require_once __DIR__ . '/test-tables/DewdropTestFruits.php';
        $table = new \DewdropFieldsTest\DewdropTestFruits($db);
        $row   = $table->createRow();

        $this->fields = new Edit($inputFilter);

        $this->fields
            ->add($row->field('name'))
            ->add($row->field('is_delicious'))
            ->add($row->field('level_of_deliciousness'));

        $this->set = new SortedSet(
            array(
                'name'   => 'dewdrop_test_sorted_set',
                'db'     => $db,
                'fields' => $this->fields
            )
        );
    }

    public function testCanIterateOverFieldCollection()
    {
        $count = 0;

        foreach ($this->set as $field) {
            $count += $field;
        }

        $this->assertEquals(3, $count);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testSettingUnknownOptionThrowsException()
    {
        $this->set->setOptions(
            array(
                'fafafafa' => null
            )
        );
    }

    public function testCanCheckForFieldsPresence()
    {
        $this->assertTrue($this->set->has('dewdrop_test_fruits:name'));
        $this->assertFalse($this->set->has('dewdrop_test_fruits:fafafafa'));
    }

    public function testCanRetrieveIndividualField()
    {
        $this->assertInstanceOf('\Dewdrop\Db\Field', $this->set->get('dewdrop_test_fruits:name'));
    }
}
