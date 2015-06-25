<?php

namespace Dewdrop\Fields;

use Dewdrop\Fields;
use Dewdrop\Pimple;
use Dewdrop\Request;
use Dewdrop\Db\Table;

class RowEditorTestModel extends Table
{
    public function init()
    {
        $this->setTableName('dewdrop_test_fruits');
    }
}

class RowEditorAnimalModel extends Table
{
    public function init()
    {
        $this->setTableName('dewdrop_test_animals');
    }
}

class RowEditorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RowEditorTestModel
     */
    private $model;

    /**
     * @var Fields
     */
    private $fields;

    /**
     * @var RowEditor
     */
    private $rowEditor;

    public function setUp()
    {
        $request = new Request();

        $this->model  = new RowEditorTestModel();
        $this->fields = new Fields();

        $this->fields
            ->add($this->model->field('name'))
            ->add($this->model->field('is_delicious'))
            ->add($this->model->field('level_of_deliciousness'));

        $this->rowEditor = new RowEditor($this->fields, $request);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testAttemptingToGetUnknownModelThrowsException()
    {
        $this->rowEditor->getModel('fafafafa');
    }

    public function testCanRetrieveModelByName()
    {
        $this->assertInstanceOf('Dewdrop\Fields\RowEditorTestModel', $this->rowEditor->getModel('dewdrop_test_fruits'));
    }

    public function testLinkMethodCallsLinkRowsToFields()
    {
        $this->rowEditor->linkByQueryString('dewdrop_test_fruits', 'dewdrop_test_fruit_id');

        /* @var $field \Dewdrop\Db\Field */
        $field = $this->fields->get('dewdrop_test_fruits:name');

        $this->assertFalse($field->hasRow());
        $this->rowEditor->link();
        $this->assertTrue($field->hasRow());
    }

    public function testCanGetRowAssociatedWithModel()
    {
        $this->rowEditor->linkByQueryString('dewdrop_test_fruits', 'dewdrop_test_fruit_id');
        $this->rowEditor->link();
        $this->assertInstanceOf('Dewdrop\Db\Row', $this->rowEditor->getRow('dewdrop_test_fruits'));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testAttemptingToRetrieveRowBeforeLinkingThrowsException()
    {
        $this->rowEditor->getRow('dewdrop_test_fruits');
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testAttemptingToRetrieveRowFromUnkownModelThrowsException()
    {
        $this->rowEditor->getRow('fafafafa');
    }

    public function testCanUseACustomLinkingCallback()
    {
        $this->rowEditor->setLinkCallback(
            function (RowEditor $rowEditor, $request) {
                $rowEditor->setRow('dewdrop_test_fruits', $this->model->createRow());
            }
        );

        $this->rowEditor->link();

        $this->assertInstanceOf('Dewdrop\Db\Row', $this->rowEditor->getRow('dewdrop_test_fruits'));
    }

    public function testIsNewWorksWithOneNewRow()
    {
        $this->rowEditor->linkByQueryString('dewdrop_test_fruits', 'dewdrop_test_fruit_id');
        $this->rowEditor->link();
        $this->assertTrue($this->rowEditor->isNew());
    }

    public function testIsNewWorksWithOneExistingRow()
    {
        $db = Pimple::getResource('db');

	$db->query('DELETE FROM dewdrop_test_fruits');
        $db->insert('dewdrop_test_fruits', array('name' => 'APPLE'));
        $id = $db->lastInsertId();

        $request   = new Request(array(), array('dewdrop_test_fruit_id' => $id));
        $rowEditor = new RowEditor($this->fields, $request);
        $rowEditor->linkByQueryString('dewdrop_test_fruits', 'dewdrop_test_fruit_id');
        $rowEditor->link();
        $this->assertFalse($rowEditor->isNew());
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testCallingIsNewPriorToLinkingThrowsException()
    {
        $this->rowEditor->isNew();
    }

    public function testCanSupplyACustomInputFilterHelper()
    {
        $inputFilterHelper = $this->getMock(
            'Dewdrop\Fields\Helper\InputFilter',
            array('addInput')
        );

        $inputFilterHelper->expects($this->any())
            ->method('addInput');

        $rowEditor = new RowEditor($this->fields, new Request(), $inputFilterHelper);
        $rowEditor->getInputFilter();
    }

    public function testIsValidWillReturnFalseWhenValidatorsAreNotSatisfied()
    {
        $this->rowEditor->linkByQueryString('dewdrop_test_fruits', 'dewdrop_test_fruit_id');
        $this->rowEditor->link();
        $this->assertFalse($this->rowEditor->isValid(array()));
    }

    public function testIsValidReturnsTrueWithValidInput()
    {
        $this->rowEditor->linkByQueryString('dewdrop_test_fruits', 'dewdrop_test_fruit_id');
        $this->rowEditor->link();
        $this->assertTrue($this->rowEditor->isValid(
            array(
                'dewdrop_test_fruits:name'                   => 'Apple',
                'dewdrop_test_fruits:level_of_deliciousness' => 8
            )
        ));
    }

    public function testCanProvideACustomSaveCallback()
    {
        $db = Pimple::getResource('db');
        $db->query('DELETE FROM dewdrop_test_fruits');

        $this->assertEquals(0, $db->fetchOne('SELECT COUNT(*) FROM dewdrop_test_fruits'));

        $this->rowEditor->linkByQueryString('dewdrop_test_fruits', 'dewdrop_test_fruit_id');
        $this->rowEditor->link();

        $this->assertTrue($this->rowEditor->isValid(
            array(
                'dewdrop_test_fruits:name'                   => 'Apple',
                'dewdrop_test_fruits:level_of_deliciousness' => 8
            )
        ));

        $this->rowEditor->setSaveCallback(
            function (RowEditor $rowEditor) {
                $rowEditor->getRow('dewdrop_test_fruits')->save();
            }
        );

        $this->rowEditor->save();

        $this->assertEquals(1, $db->fetchOne('SELECT COUNT(*) FROM dewdrop_test_fruits'));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testCallingLinkWithNoLinkRulesThrowsException()
    {
        $this->rowEditor->link();
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testSettingRowFromUnknownModelInstanceThrowsException()
    {
        $unknownModel = new RowEditorTestModel();
        $row = $unknownModel->createRow();
        $this->rowEditor->setRow('dewdrop_test_fruits', $row);
    }

    public function testCanSaveWithoutACustomCallback()
    {
        $db = Pimple::getResource('db');
        $db->query('DELETE FROM dewdrop_test_fruits');

        $this->assertEquals(0, $db->fetchOne('SELECT COUNT(*) FROM dewdrop_test_fruits'));

        $this->rowEditor->linkByQueryString('dewdrop_test_fruits', 'dewdrop_test_fruit_id');
        $this->rowEditor->link();

        $this->assertTrue($this->rowEditor->isValid(
            array(
                'dewdrop_test_fruits:name'                   => 'Apple',
                'dewdrop_test_fruits:level_of_deliciousness' => 8
            )
        ));

        $this->rowEditor->save();

        $this->assertEquals(1, $db->fetchOne('SELECT COUNT(*) FROM dewdrop_test_fruits'));
    }

    public function testSavingWillTraverseLinkedFieldsToHookRowsTogether()
    {
        $db = Pimple::getResource('db');
        $db->query('DELETE FROM dewdrop_test_fruits');

        $animalModel = new RowEditorAnimalModel();

        $this->fields->add($animalModel->field('name'));
        $this->fields->add($animalModel->field('is_fierce'));
        $this->fields->add($animalModel->field('is_cute'));

        $this->rowEditor->linkByQueryString('dewdrop_test_animals', 'dewdrop_test_animal_id');
        $this->rowEditor->linkByField('dewdrop_test_fruits', $animalModel->field('favorite_fruit_id'));
        $this->rowEditor->link();

        $this->assertTrue($this->rowEditor->isValid(
            array(
                'dewdrop_test_fruits:name'                   => 'Banana',
                'dewdrop_test_fruits:level_of_deliciousness' => 8,
                'dewdrop_test_animals:name'                  => 'Gorilla',
                'dewdrop_test_animals:is_fierce'             => 1,
                'dewdrop_test_animals:is_cute'               => 1
            )
        ));

        $this->rowEditor->save();

        $this->assertEquals(
            $db->fetchOne('SELECT MAX(dewdrop_test_fruit_id) FROM dewdrop_test_fruits'),
            $db->fetchOne(
                'SELECT favorite_fruit_id FROM dewdrop_test_animals ORDER BY dewdrop_test_animal_id DESC LIMIT 1'
            )
        );
    }
}
