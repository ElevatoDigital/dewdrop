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
        $method    = (count($post) ? 'POST' : 'GET');
        $request   = new Request($post, $query, $method);
        $component = $this->getComponent($request);

        $response = $this->testCase->getMock(
            '\Dewdrop\Admin\Response',
            array('render', 'executeHelper'),
            array()
        );

        $component->route($name, $response);

        return $response;
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
        $wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
        $db   = new Adapter($wpdb);

        $paths = new Paths();
        $file  = $paths->getAdmin() . '/' . $this->componentFolder . '/Component.php';

        $className = 'Admin\\' . $this->componentNamespace . '\\Component';

        require_once $file;
        return new $className($db, $paths, $request);
    }
}
