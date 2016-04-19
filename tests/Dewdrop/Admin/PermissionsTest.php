<?php

namespace Dewdrop\Admin;

use Dewdrop\Admin\Component\ComponentInterface;
use Dewdrop\Admin\Component\ComponentTrait;
use Dewdrop\Db\Table;

class PermissionsTestComponent implements ComponentInterface
{
    use ComponentTrait;

    public function url($page, array $params = [])
    {

    }

    public function init()
    {
        // TODO: Implement init() method.
    }

    public function preDispatch()
    {
        // TODO: Implement preDispatch() method.
    }

    public function hasPimpleResource($name)
    {
        // TODO: Implement hasPimpleResource() method.
    }

    public function getPimpleResource($name)
    {
        // TODO: Implement getPimpleResource() method.
    }

    public function getTitle()
    {
        return 'Test Component';
    }

    public function getName()
    {
        return 'component';
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
