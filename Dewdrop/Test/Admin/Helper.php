<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Test\Admin;

use Dewdrop\Admin\Response;
use Dewdrop\Db\Adapter;
use Dewdrop\Paths;
use Dewdrop\Request;
use PHPUnit_Framework_TestCase as TestCase;
use wpdb;

/**
 * This helper is used by all admin test case classes to provide the ability
 * to easily get access to the tested component object and dispatch pages from
 * it.  This is used to allow some "horizontal" reuse between the admin test
 * cases because we can't achieve the reuse with inheritance.  PHPUnit has a
 * pre-defined hierarchy imposed by its DBUnit extension, so we need to compose
 * our additional functionality in this manner.
 */
class Helper
{
    /**
     * The DB adapter used by generated components and pages.
     *
     * @var \Dewdrop\Db\Adapter
     */
    private $db;

    /**
     * The PHPUnit test case this helper was created by.
     *
     * @var PHPUnit_Framework_TestCase
     */
    private $testCase;

    /**
     * The admin sub-folder the component is located in.
     *
     * @var string
     */
    private $componentFolder;

    /**
     * The namespace the component classes are assigned to.
     *
     * @var string
     */
    private $componentNamespace;

    /**
     * Whether to mock the execution of response helper actions.
     *
     * @var boolean
     */
    private $mockResponseHelper = false;

    /**
     * Create new helper object for the supplied test case.
     *
     * @param TestCase $testCase
     * @param string $componentFolder
     * @param string $componentNamespace
     */
    public function __construct(TestCase $testCase, $componentFolder, $componentNamespace)
    {
        $this->testCase           = $testCase;
        $this->componentFolder    = $componentFolder;
        $this->componentNamespace = $componentNamespace;
    }

    /**
     * Whether to mock the execution of the queued response helper actions.
     *
     * @param boolean $mockResponseHelper
     * @return \Dewdrop\Test\Admin\Helper
     */
    public function setMockResponseHelper($mockResponseHelper)
    {
        $this->mockResponseHelper = $mockResponseHelper;

        return $this;
    }

    /**
     * Dispatch a page, using the supplied page name (e.g. "Index" or "Edit")
     * and request data.  If you supply any POST data, the request method will
     * be set to POST as well.
     *
     * @param string $name
     * @param array $post
     * @param array $query
     * @return \Dewdrop\Admin\Response\MockResponse
     */
    public function dispatchPage($name, array $post = array(), array $query = array())
    {
        $request   = $this->createRequest($post, $query);
        $component = $this->getComponent($request);

        $mockedMethods = array('render');

        if ($this->mockResponseHelper) {
            $mockedMethods[] = 'executeHelper';
        }

        $response = $this->testCase->getMock(
            '\Dewdrop\Admin\Response',
            $mockedMethods,
            array()
        );

        $component->route($name, $response);

        return $response;
    }

    /**
     * Get an object for the named page.  Allows you to play with the page outside
     * the stock dispatch loop implemented in ComponentAbstract.
     *
     * @param string $name
     * @param array $post
     * @param array $query
     * @return \Dewdrop\Admin\Page\PageAbstract
     */
    public function getPage($name, $post = array(), $query = array())
    {
        $request   = $this->createRequest($post, $query);
        $component = $this->getComponent($request);

        return $component->createPageObject($name);
    }

    /**
     * Complete the init and process portions of the dispatch loop so that we can
     * return the response helper for testing.
     *
     * @param string $name
     * @param array $post
     * @param array $query
     * @return \Dewdrop\Admin\ResponseHelper\Standard
     */
    public function getResponseHelper($name, $post = array(), $query = array())
    {
        $page   = $this->getPage($name, $post, $query);
        $helper = null;

        $page->init();

        if ($page->shouldProcess()) {
            $helper = $page->createResponseHelper();

            $page->process($helper);
        }

        return $helper;
    }

    /**
     * Get a component object, injecting the supplied request so that we can
     * test different responses.
     *
     * @param Request $request
     * @return \Dewdrop\Admin\ComponentAbstract
     */
    public function getComponent(Request $request)
    {
        $paths = new Paths();
        $file  = $paths->getAdmin() . '/' . $this->componentFolder . '/Component.php';

        $className = 'Admin\\' . $this->componentNamespace . '\\Component';

        require_once $file;
        return new $className($this->getDb(), $paths, $request);
    }

    /**
     * Get a Dewdrop DB adapter.
     *
     * @return \Dewdrop\Db\Adapter
     */
    public function getDb()
    {
        if (!$this->db) {
            $wpdb     = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
            $this->db = new Adapter($wpdb);
        }

        return $this->db;
    }

    /**
     * Create a request using the supplied POST and GET values.  If there are any
     * POST values, we set the request method to POST.
     *
     * @param array $post
     * @param arary $query
     * @return \Dewdrop\Request
     */
    private function createRequest($post = array(), $query = array())
    {
        $method  = (count($post) ? 'POST' : 'GET');
        $request = new Request($post, $query, $method);

        return $request;
    }
}
