<?php

namespace Dewdrop\Fields;

use Dewdrop\Fields;
use PHPUnit_Framework_TestCase;

class FieldTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Field
     */
    private $field;

    private $user;

    private $permissions = array(
        'visibility' => 'visible',
        'filtering'  => 'filterable',
        'editing'    => 'editable',
        'sorting'    => 'sortable'
    );

    public function setUp()
    {
        $this->field = new Field();
        $this->field->setId('test');

        $this->user = $this->getMock(
            '\Dewdrop\Fields\UserInterface',
            array('hasRole'),
            array()
        );

        $this->user->expects($this->any())
            ->method('hasRole')
            ->with('admin')
            ->will($this->returnValue(true));
    }

    public function testGetIdReturnsFieldId()
    {
        $this->assertEquals('test', $this->field->getId());
    }

    public function testCanGloballyAllowAllFourPermissions()
    {
        foreach ($this->permissions as $permission) {
            $setter = 'set' . ucfirst($permission);
            $getter = 'get' . ucfirst($permission) . 'Setting';
            $check  = 'is' . ucfirst($permission);


            $this->assertFalse($this->field->$check());

            $this->field->$setter(true);

            $this->assertEquals(array(Field::AUTHORIZATION_ALLOW_ALL), $this->field->$getter());
            $this->assertTrue($this->field->$check());
            $this->assertTrue($this->field->$check($this->user));
        }
    }

    public function testCanGloballyForbidAllFourPermissions()
    {
        foreach ($this->permissions as $permission) {
            $setter = 'set' . ucfirst($permission);
            $getter = 'get' . ucfirst($permission) . 'Setting';
            $check  = 'is' . ucfirst($permission);


            $this->assertFalse($this->field->$check());

            $this->field->$setter(false);

            $this->assertEquals(array(), $this->field->$getter());
            $this->assertFalse($this->field->$check());
            $this->assertFalse($this->field->$check($this->user));
        }
    }

    public function testCanGrantAllFourPermissionsToASpecificRole()
    {
        foreach ($this->permissions as $permission) {
            $setter = 'set' . ucfirst($permission);
            $getter = 'get' . ucfirst($permission) . 'Setting';
            $check  = 'is' . ucfirst($permission);


            $this->assertFalse($this->field->$check());

            $this->field->$setter(array('admin'));

            $this->assertEquals(array('admin'), $this->field->$getter());
            $this->assertFalse($this->field->$check());
            $this->assertTrue($this->field->$check($this->user));
        }
    }

    public function testCanAllowAllFourPermissionsForASpecificRole()
    {
        $adminUser = $this->getMock(
            '\Dewdrop\Fields\UserInterface',
            array('hasRole'),
            array()
        );

        $adminUser->expects($this->any())
            ->method('hasRole')
            ->with('admin')
            ->will($this->returnValue(true));

        $otherRoleUser = $this->getMock(
            '\Dewdrop\Fields\UserInterface',
            array('hasRole'),
            array()
        );

        $otherRoleUser->expects($this->any())
            ->method('hasRole')
            ->with('admin')
            ->will($this->returnValue(false));

        foreach ($this->permissions as $allowForbidForm => $permission) {
            $setter = 'set' . ucfirst($permission);
            $getter = 'get' . ucfirst($permission) . 'Setting';
            $check  = 'is' . ucfirst($permission);
            $allow  = 'allow' . ucfirst($allowForbidForm) . 'ForRole';


            $this->field->$allow('admin');

            $this->assertEquals(array('admin'), $this->field->$getter());

            $this->assertFalse($this->field->$check($otherRoleUser));
            $this->assertTrue($this->field->$check($adminUser));
        }
    }

    public function testCanForbidAllFourPermissionsToASpecificRole()
    {
        $adminUser = $this->getMock(
            '\Dewdrop\Fields\UserInterface',
            array('hasRole'),
            array()
        );

        $adminUser->expects($this->any())
            ->method('hasRole')
            ->with('other_role')
            ->will($this->returnValue(false));

        $otherRoleUser = $this->getMock(
            '\Dewdrop\Fields\UserInterface',
            array('hasRole'),
            array()
        );

        $otherRoleUser->expects($this->any())
            ->method('hasRole')
            ->with('other_role')
            ->will($this->returnValue(true));

        foreach ($this->permissions as $allowForbidForm => $permission) {
            $setter = 'set' . ucfirst($permission);
            $getter = 'get' . ucfirst($permission) . 'Setting';
            $check  = 'is' . ucfirst($permission);
            $forbid = 'forbid' . ucfirst($allowForbidForm) . 'ForRole';


            $this->field->$setter(array('other_role', 'admin'));
            $this->assertEquals(array('other_role', 'admin'), $this->field->$getter());

            $this->field->$forbid('admin');

            $this->assertEquals(array('other_role'), $this->field->$getter());

            $this->assertTrue($this->field->$check($otherRoleUser));
            $this->assertFalse($this->field->$check($adminUser));
        }
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testCallingAddWithNoFieldsSetThrowsException()
    {
        $this->field->add('will_throw');
    }

    public function testCanAddAFieldToFieldsSetViaAddMethod()
    {
        $set = new Fields();
        $set->add($this->field);

        $this->assertEquals(1, count($set));

        $this->field->add('will_add');

        $this->assertEquals(2, count($set));
        $this->assertTrue($set->has('will_add'));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testCanResetFieldsSet()
    {
        $set = new Fields();
        $this->field->setFieldsSet($set);
        $this->field->add('should_work');
        $this->field->resetFieldsSet();
        $this->field->add('should_throw');
    }

    public function testCanSetNote()
    {
        $this->field->setNote('TEST_NOTE');
        $this->assertEquals('TEST_NOTE', $this->field->getNote());
    }

    public function testCanSetMultipleOptionsViaSetOptionsMethod()
    {
        $this->field->setOptions(
            array(
                'visible'  => array('admin'),
                'editable' => array('other'),
                'note'     => 'TEST_NOTE'
            )
        );

        $this->assertEquals(array('admin'), $this->field->getVisibleSetting());
        $this->assertEquals(array('other'), $this->field->getEditableSetting());
        $this->assertEquals('TEST_NOTE', $this->field->getNote());
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testSettingAnInvalidPermissionValueThrowsAnException()
    {
        $this->field->setVisible(5);
    }

    public function testCanAssignHelperCallback()
    {
        $this->field->assignHelperCallback('Helper', function () {});
        $this->assertTrue($this->field->hasHelperCallback('Helper'));
        $this->assertTrue(is_callable($this->field->getHelperCallback('Helper')));
    }

    public function testCanGetAllHelperCallbacks()
    {
        $this->assertEquals(0, count($this->field->getAllHelperCallbacks()));
        $this->field->assignHelperCallback('Helper.1', function () {});
        $this->field->assignHelperCallback('Helper.2', function () {});
        $this->assertEquals(2, count($this->field->getAllHelperCallbacks()));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testCallingSetOptionsWithInvalidOptionsThrowsException()
    {
        $this->field->setOptions(array('invalid_option_name', false));
    }

    public function testAddingFieldToAdditionalFieldsetDoesNotOverrideOriginalSet()
    {
        $orig = new Fields();
        $orig->add($this->field);

        $second = new Fields();
        $second->add($this->field);

        $this->field->add('test');

        $this->assertTrue($orig->has('test'));
        $this->assertEquals(2, count($orig));
    }

    public function testCanSetAndGetValue()
    {
        $this->field->setValue(123);
        $this->assertEquals(123, $this->field->getValue());
    }

    public function testCanSetAndGetLabel()
    {
        $this->field->setLabel('LABEL');
        $this->assertEquals('LABEL', $this->field->getLabel());
    }

    public function testQueryStringIdIsEquivalentToId()
    {
        $this->field->setId('test');
        $this->assertEquals('test', $this->field->getQueryStringId());
    }

    public function testHtmlIdIsEquivalentToId()
    {
        $this->field->setId('test');
        $this->assertEquals('test', $this->field->getHtmlId());
    }
}
