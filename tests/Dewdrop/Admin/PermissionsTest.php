<?php

namespace Dewdrop\Admin;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Db\Table;

class PermissionsTestComponent extends ComponentAbstract
{
    public function init()
    {
        $this->setTitle('TEST');
    }
}

class PermissionsTestModel extends Table
{
    public function init()
    {
        $this->setTableName('dewdrop_test_fruits');
    }
}

class PermissionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Permissions
     */
    private $permissions;

    public function setUp()
    {
        $component = new PermissionsTestComponent();

        $this->permissions = $component->getPermissions();
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testSettingUnknownPermissionThrowsException()
    {
        $this->permissions->set('foobar', true);
    }

    public function testCrudInterfaceComponentsGetExtraPermissionsRegistered()
    {
        $component = $this->getMock(
            '\Dewdrop\Admin\Component\CrudInterface',
            array(
                'checkRequiredProperties',
                'getTitle',
                'getListing',
                'getFields',
                'getPrimaryModel',
                'getFieldGroupsFilter',
                'getVisibilityFilter',
                'getRowEditor'
            ),
            array()
        );

        $model = new PermissionsTestModel();

        $component->expects($this->any())
            ->method('getPrimaryModel')
            ->will($this->returnValue($model));

        $permissions = new Permissions($component);

        $this->assertTrue($permissions->can('create'));
        $this->assertTrue($permissions->can('edit'));
    }

    public function testSetAllAffectsAllRegisteredPermissions()
    {
        $this->assertTrue($this->permissions->can('access'));
        $this->assertTrue($this->permissions->can('display-menu'));

        $this->permissions->setAll(false);

        $this->assertFalse($this->permissions->can('access'));
        $this->assertFalse($this->permissions->can('display-menu'));
    }
}
