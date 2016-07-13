<?php

namespace Dewdrop;

use Dewdrop\Fields;
use Dewdrop\Fields\Field;
use Dewdrop\Fields\Filter\Callback as CallbackFilter;
use PHPUnit_Framework_TestCase;
use stdClass;

class FieldsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Fields
     */
    private $fields;

    public function setUp()
    {
        $this->fields = new Fields();

        $this->fields
            ->add('visible')
                ->setVisible(true)
            ->add('sortable')
                ->setSortable(true)
            ->add('editable')
                ->setEditable(true)
            ->add('filterable')
                ->setFilterable(true);
    }

    public function testCallingAddWithStringCreatesCustomField()
    {
        $this->fields->add('test');

        $this->assertTrue($this->fields->has('test'));
        $this->assertInstanceOf('Dewdrop\Fields\FieldInterface', $this->fields->get('test'));
    }

    public function testGetVisibleFieldsOnlyReturnsVisibleFields()
    {
        $fields = $this->fields->getVisibleFields();

        $this->assertInstanceOf('Dewdrop\Fields', $fields);
        $this->assertEquals(1, count($fields));
        $this->assertTrue($fields->has('visible'));
    }

    public function testGetSortableFieldsOnlyReturnsSortableFields()
    {
        $fields = $this->fields->getSortableFields();

        $this->assertInstanceOf('Dewdrop\Fields', $fields);
        $this->assertEquals(1, count($fields));
        $this->assertTrue($fields->has('sortable'));
    }

    public function testGetEditableFieldsOnlyReturnsEditableFields()
    {
        $fields = $this->fields->getEditableFields();

        $this->assertInstanceOf('Dewdrop\Fields', $fields);
        $this->assertEquals(1, count($fields));
        $this->assertTrue($fields->has('editable'));
    }

    public function testGetFilterableFieldsOnlyReturnsFilterableFields()
    {
        $fields = $this->fields->getFilterableFields();

        $this->assertInstanceOf('Dewdrop\Fields', $fields);
        $this->assertEquals(1, count($fields));
        $this->assertTrue($fields->has('filterable'));
    }

    public function testCanPerformNestedIterationsOfAFieldsCollection()
    {
        $first = $this->fields->getIterator()->current();

        /* @var $field Field */
        foreach ($this->fields as $field) {
            $this->assertEquals($first->getId(), $field->getId());

            /* @var $fieldInner Field */
            foreach ($this->fields as $fieldInner) {
                $this->assertEquals($first->getId(), $fieldInner->getId());
                break;
            }

            break;
        }
    }

    public function testCanUsePhpsCountFunctionToCountFields()
    {
        $this->assertEquals(4, count($this->fields));
    }

    public function testCanAddAFieldUsingTheArrayInterface()
    {
        $field = new Field();
        $field->setId('array');

        /* @var $this->fields Fields */
        $this->fields[] = $field;
        $this->assertTrue($this->fields->has('array'));
    }

    public function testAddingAFieldWithStringOffsetInArrayInterfaceChangesId()
    {
        $field = new Field();
        $field->setId('array');
        $this->fields['new_id'] = $field;
        $this->assertTrue($this->fields->has('new_id'));
        $this->assertFalse($this->fields->has('array'));
    }

    public function testConstructorAcceptsBothStringsAndFieldInterfaceObjects()
    {
        $field = new Field();
        $field->setId('object');

        $fields = new Fields(array($field, 'string'));

        $this->assertTrue($fields->has('object'));
        $this->assertTrue($fields->has('string'));
    }

    public function testCanRetrieveFieldsUsingTheArrayInterface()
    {
        $this->assertInstanceOf('Dewdrop\Fields\Field', $this->fields['visible']);
        $this->assertEquals('visible', $this->fields['visible']->getId());
    }

    public function testCanRemoveAFieldUsingUnset()
    {
        $this->assertTrue($this->fields->has('visible'));
        unset($this->fields['visible']);
        $this->assertFalse($this->fields->has('visible'));
    }

    public function testCanCheckThatAFieldExistWithIsset()
    {
        $this->assertTrue(isset($this->fields['visible']));
        $this->assertFalse(isset($this->fields['fafafafa']));
    }

    public function testGettingAnUnknownFieldReturnsNull()
    {
        $this->assertNull($this->fields->get('fafafafa'));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testAddingInvalidObjectThrowsException()
    {
        $this->fields->add(new stdClass);
    }

    public function testAddingFieldWithDuplicateIdViaArrayInterfaceWithReplacePreviousField()
    {
        $field = new Field();
        $field->setId('visible');
        $newHash = spl_object_hash($field);

        $oldHash = spl_object_hash($this->fields->get('visible'));

        $this->assertEquals($oldHash, spl_object_hash($this->fields->get('visible')));

        $this->fields['visible'] = $field;

        $this->assertEquals($newHash, spl_object_hash($this->fields->get('visible')));

        $count = 0;

        /* @var $field Field */
        foreach ($this->fields as $field) {
            if ('visible' === $field->getId()) {
                $count += 1;
            }
        }

        $this->assertEquals(1, $count);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testAddingAnInvalidObjectViaTheArrayInterfaceThrowsAndException()
    {
        $this->fields['test'] = new stdClass();
    }

    public function testCallingGetAllReturnsSameInstance()
    {
        $this->assertEquals(spl_object_hash($this->fields), spl_object_hash($this->fields->getAll()));
    }

    public function testCanSupplyASingleFilterToGetAll()
    {
        $filter = new CallbackFilter(
            function (Field $field) {
                return 'visible' === $field->getId();
            }
        );

        $this->assertEquals(1, count($this->fields->getAll($filter)));
    }

    public function testCanSupplyMultipleFiltersToGetAll()
    {
        $firstFilter = new CallbackFilter(
            function (Field $field) {
                return 'visible' !== $field->getId();
            }
        );

        $this->assertEquals(3, count($this->fields->getAll($firstFilter)));

        $secondFilter = new CallbackFilter(
            function (Field $field) {
                return 'filterable' !== $field->getId();
            }
        );

        $this->assertEquals(2, count($this->fields->getAll(array($firstFilter, $secondFilter))));
    }

    public function testCanFilterWhenGettingVisibleFields()
    {
        /* @var $field Field */
        foreach ($this->fields as $field) {
            $field->setVisible(true);
        }

        $this->assertEquals(4, count($this->fields->getVisibleFields()));

        $filter = new CallbackFilter(
            function (Field $field) {
                return 'visible' === $field->getId();
            }
        );

        $this->assertEquals(1, count($this->fields->getVisibleFields($filter)));
    }

    public function testCanFilterWhenGettingSortableFields()
    {
        /* @var $field Field */
        foreach ($this->fields as $field) {
            $field->setSortable(true);
        }

        $this->assertEquals(4, count($this->fields->getSortableFields()));

        $filter = new CallbackFilter(
            function (Field $field) {
                return 'visible' === $field->getId();
            }
        );

        $this->assertEquals(1, count($this->fields->getSortableFields($filter)));
    }

    public function testCanFilterWhenGettingEditableFields()
    {
        /* @var $field Field */
        foreach ($this->fields as $field) {
            $field->setEditable(true);
        }

        $this->assertEquals(4, count($this->fields->getEditableFields()));

        $filter = new CallbackFilter(
            function (Field $field) {
                return 'visible' === $field->getId();
            }
        );

        $this->assertEquals(1, count($this->fields->getEditableFields($filter)));
    }

    public function testCanFilterWhenGettingFilterableFields()
    {
        foreach ($this->fields as $field) {
            $field->setFilterable(true);
        }

        $this->assertEquals(4, count($this->fields->getFilterableFields()));

        $filter = new CallbackFilter(
            function (Field $field) {
                return 'visible' === $field->getId();
            }
        );

        $this->assertEquals(1, count($this->fields->getFilterableFields($filter)));
    }

    public function testAddingDbFieldWithNoModelNameUsesTableName()
    {
        require_once __DIR__ . '/Db/table/DewdropTestFruits.php';
        $model = new \DewdropTest\DewdropTestFruits();

        $this->fields->add($model->field('name'));

        $model = current(array_keys($this->fields->getModelsByName()));

        $this->assertEquals('dewdrop_test_fruits', $model);
    }

    public function testAddingDbFieldWithCustomModelNameUsesThatName()
    {
        require_once __DIR__ . '/Db/table/DewdropTestFruits.php';
        $model = new \DewdropTest\DewdropTestFruits();

        $this->fields->add($model->field('name'), 'fafafafa');

        $name = current(array_keys($this->fields->getModelsByName()));

        $this->assertEquals('fafafafa', $name);
        $this->assertEquals('fafafafa', $model->field('name')->getGroupName());
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testAddingFieldsFromMultipleInstancesOfTheSameModelWithoutCustomNameThrowsException()
    {
        require_once __DIR__ . '/Db/table/DewdropTestFruits.php';
        $one = new \DewdropTest\DewdropTestFruits();
        $two = new \DewdropTest\DewdropTestFruits();

        $this->fields
            ->add($one->field('name'))
            ->add($two->field('name'));
    }

    public function testAddingFieldsFromMultipleInstancesOfTheSameModelWithCustomNamesWorks()
    {
        require_once __DIR__ . '/Db/table/DewdropTestFruits.php';
        $one = new \DewdropTest\DewdropTestFruits();
        $two = new \DewdropTest\DewdropTestFruits();

        $this->fields
            ->add($one->field('name'), 'one')
            ->add($two->field('name'), 'two');

        $modelNames = array_keys($this->fields->getModelsByName());

        $this->assertContains('one', $modelNames);
        $this->assertContains('two', $modelNames);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testUsingDifferentModelsWithTheSameNameThrowsException()
    {
        require_once __DIR__ . '/Db/table/DewdropTestFruits.php';
        $fruits = new \DewdropTest\DewdropTestFruits();

        require_once __DIR__ . '/Db/table/DewdropTestAnimals.php';
        $animals = new \DewdropTest\DewdropTestAnimals();

        $this->fields
            ->add($fruits->field('name'), 'duplicate')
            ->add($animals->field('name'), 'duplicate');
    }

    public function testCanSetUserObject()
    {
        require_once __DIR__ . '/fields-test/TestUser.php';
        $user = new \DewdropTest\TestUser();

        $this->fields->setUser($user);

        $this->assertInstanceOf('Dewdrop\Fields\UserInterface', $this->fields->getUser());
    }

    public function testCanReferenceFieldIdsWhileIterating()
    {
        /* @var $field Field */
        foreach ($this->fields as $id => $field) {
            $this->assertEquals('visible', $id);
            $this->assertEquals('visible', $field->getId());
            break;
        }
    }

    public function testCanRetrievePositionForExistingField()
    {
        $this->assertTrue($this->fields->has('visible', $visiblePosition));
        $this->assertSame(0, $visiblePosition);

        $this->assertTrue($this->fields->has('sortable', $sortablePosition));
        $this->assertSame(1, $sortablePosition);

        $this->assertTrue($this->fields->has('editable', $editablePosition));
        $this->assertSame(2, $editablePosition);

        $this->assertTrue($this->fields->has('filterable', $filterablePosition));
        $this->assertSame(3, $filterablePosition);
    }

    public function testCanInsertAFieldIntoAPositionImmediatelyAfterAnExistingField()
    {
        // Insert a field after the first field
        $this->assertInstanceOf('Dewdrop\Fields\FieldInterface', $this->fields->insertAfter('second', 'visible'));

        $this->assertCount(5, $this->fields);

        $this->assertTrue($this->fields->has('visible', $visiblePosition));
        $this->assertSame(0, $visiblePosition);

        $this->assertTrue($this->fields->has('second', $secondPosition));
        $this->assertSame(1, $secondPosition);

        $this->assertTrue($this->fields->has('sortable', $sortablePosition));
        $this->assertSame(2, $sortablePosition);

        $this->assertTrue($this->fields->has('editable', $editablePosition));
        $this->assertSame(3, $editablePosition);

        $this->assertTrue($this->fields->has('filterable', $filterablePosition));
        $this->assertSame(4, $filterablePosition);

        // Insert a field after some field in the middle
        $this->assertInstanceOf('Dewdrop\Fields\FieldInterface', $this->fields->insertAfter('fourth', 'sortable'));

        $this->assertCount(6, $this->fields);

        $this->assertTrue($this->fields->has('visible', $visiblePosition));
        $this->assertSame(0, $visiblePosition);

        $this->assertTrue($this->fields->has('second', $secondPosition));
        $this->assertSame(1, $secondPosition);

        $this->assertTrue($this->fields->has('sortable', $sortablePosition));
        $this->assertSame(2, $sortablePosition);

        $this->assertTrue($this->fields->has('fourth', $sortablePosition));
        $this->assertSame(3, $sortablePosition);

        $this->assertTrue($this->fields->has('editable', $editablePosition));
        $this->assertSame(4, $editablePosition);

        $this->assertTrue($this->fields->has('filterable', $filterablePosition));
        $this->assertSame(5, $filterablePosition);

        // Insert a field after the last field
        $this->assertInstanceOf('Dewdrop\Fields\FieldInterface', $this->fields->insertAfter('last', 'filterable'));

        $this->assertCount(7, $this->fields);

        $this->assertTrue($this->fields->has('visible', $visiblePosition));
        $this->assertSame(0, $visiblePosition);

        $this->assertTrue($this->fields->has('second', $secondPosition));
        $this->assertSame(1, $secondPosition);

        $this->assertTrue($this->fields->has('sortable', $sortablePosition));
        $this->assertSame(2, $sortablePosition);

        $this->assertTrue($this->fields->has('fourth', $sortablePosition));
        $this->assertSame(3, $sortablePosition);

        $this->assertTrue($this->fields->has('editable', $editablePosition));
        $this->assertSame(4, $editablePosition);

        $this->assertTrue($this->fields->has('filterable', $filterablePosition));
        $this->assertSame(5, $filterablePosition);

        $this->assertTrue($this->fields->has('last', $filterablePosition));
        $this->assertSame(6, $filterablePosition);
    }
}
