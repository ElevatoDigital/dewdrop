<?php

namespace Dewdrop\Admin\Component;

use Dewdrop\Admin\Response;
use Dewdrop\Db\Adapter;
use Dewdrop\Paths;
use Dewdrop\Pimple;
use Dewdrop\Request;
use Dewdrop\Test\BaseTestCase;

class ComponentAbstractTest extends BaseTestCase
{
    private $db;

    private $paths;

    private $request;

    public function setUp()
    {
        $testPimple = new \Pimple();
        $testPimple['dewdrop-request'] = new Request();

        require_once __DIR__ . '/../test-components/animals/Component.php';
        $this->component = new \DewdropTest\Admin\Animals\Component($testPimple);

        $this->request = $this->component->getRequest();
        $this->paths   = $this->component->getPaths();
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testComponentWithEmptyInitThrowsException()
    {
        require_once __DIR__ . '/../test-components/insufficient-init-method/Component.php';
        $component = new \DewdropTest\Admin\InsufficientInitMethod\Component(Pimple::getInstance());
    }

    public function testGetDbReturnsAdapter()
    {
        $this->assertInstanceOf('\Dewdrop\Db\Adapter', $this->component->getDb());
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
}
