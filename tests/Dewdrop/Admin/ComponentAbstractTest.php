<?php

namespace Dewdrop\Admin;

use Dewdrop\Admin\Response;
use Dewdrop\Db\Adapter;
use Dewdrop\Paths;
use Dewdrop\Request;
use Dewdrop\Test\BaseTestCase;

class ComponentAbstractTest extends BaseTestCase
{
    private $db;

    private $paths;

    private $request;

    public function setUp()
    {
        $this->paths   = new Paths();
        $this->request = new Request();

        $wpdb = new \wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
        $this->db = new Adapter($wpdb);

        require_once __DIR__ . '/test-components/animals/Component.php';
        $this->component = new \DewdropTest\Admin\Animals\Component($this->db, $this->paths, $this->request);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testComponentWithEmptyInitThrowsException()
    {
        require_once __DIR__ . '/test-components/insufficient-init-method/Component.php';
        $component = new \DewdropTest\Admin\InsufficientInitMethod\Component($this->db, $this->paths, $this->request);
    }

    public function testGetDbReturnsAdapter()
    {
        $this->assertInstanceOf('\Dewdrop\Db\Adapter', $this->component->getDb());
    }

    public function testWillRouteToPageSpecifiedInRouteQueryParam()
    {
        $this->request->setQuery('route', 'RouteTest');

        $response = $this->getMock(
            '\Dewdrop\Admin\Response',
            array('render', 'setPage'),
            array()
        );

        $response
            ->expects($this->once())
            ->method('setPage')
            ->with(new \PHPUnit_Framework_Constraint_IsInstanceOf('\DewdropTest\Admin\Animals\RouteTest'));

        $this->component->route(null, $response);
    }

    public function testWillRouteToIndexPageWhenNothingSpecifiedInQueryString()
    {
        $response = $this->getMock(
            '\Dewdrop\Admin\Response',
            array('render', 'setPage'),
            array()
        );

        $response
            ->expects($this->once())
            ->method('setPage')
            ->with(new \PHPUnit_Framework_Constraint_IsInstanceOf('\DewdropTest\Admin\Animals\Index'));

        $this->component->route(null, $response);
    }

    public function testGeneratesUrlForSpecifiedPageWithNoSubmenusOrParams()
    {
        $this->assertContains(
            '&route=MyCrazyPageName',
            $this->component->url('MyCrazyPageName')
        );
    }

    public function testUrlMethodWillAppendSuppliedQueryParams()
    {
        $this->assertContains(
            '&param1=1&param2=2',
            $this->component->url('Page', array('param1' => 1, 'param2' => 2))
        );
    }

    /**
     * We're actually hitting WP here, so it's hard to really test anything worthwhile.
     */
    public function testRegisterMethodDoesntTotallyFail()
    {
        $this->component->register();
    }

    public function testSubmenuUrlUsesPageParamInsteadOfRoute()
    {
        $this->component->addToSubmenu('Test', 'Test');

        $this->assertContains(
            'page=Animals/Test',
            $this->component->url('Test')
        );

        $this->assertNotContains(
            'route=Test',
            $this->component->url('Test')
        );
    }

    public function testIndexUrlWithMatchingSubmenuUsesDefaultComponentRoute()
    {
        $this->component->addToSubmenu('View All', 'Index');

        $this->assertContains(
            'page=Animals',
            $this->component->url('Index')
        );

        $this->assertNotContains(
            'page=Animals/Index',
            $this->component->url('Index')
        );
    }

    public function testAddingParamsEvenWithSubmenuUsesRouteQueryParam()
    {
        $this->component->addToSubmenu('Test', 'Test');

        $this->assertContains(
            'route=Test&id=3',
            $this->component->url('Test', array('id' => 3))
        );

        $this->assertNotContains(
            'page=Animals/Test',
            $this->component->url('Test', array('id' => 3))
        );
    }

    public function testRegisterMenuPageManipulatesSubmenuFileGlobalWhenMatched()
    {
        global $submenu_file;

        $this->component
            ->addToSubmenu('View All', 'Index')
            ->addToSubmenu('Edit', 'Edit');

        $this->request->setQuery('page', 'Animals/Edit');

        $this->component->registerMenuPage();

        $this->assertEquals('Animals/Edit', $submenu_file);
    }

    public function testCanSetMenuPositionAndIcon()
    {
        $component = $this->getMock(
            '\DewdropTest\Admin\Animals\Component',
            array('addObjectPage', 'getSlug'),
            array($this->db, $this->paths, $this->request)
        );

        // Forcing getSlug() to return Animals because otherwise the mock
        // object class will be used.
        $component
            ->expects($this->any())
            ->method('getSlug')
            ->will($this->returnValue('Animals'));

        $component
            ->expects($this->once())
            ->method('addObjectPage')
            ->with(
                $this->equalTo('Animals'),
                $this->equalTo('Animals'),
                $this->equalTo('add_users'),
                $this->equalTo('Animals'),
                $this->equalTo(array($component, 'route')),
                $this->equalTo('test.png'),
                $this->equalTo(6)
            );

        $component
            ->setMenuPosition(6)
            ->setIcon('test.png');

        $component->registerMenuPage();
    }
}
