<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin;

use Dewdrop\Admin\Response;
use Dewdrop\Db\Adapter;
use Dewdrop\Paths;
use Dewdrop\Request;
use Zend\Dom\Query as DomQuery;
use PHPUnit_Framework_TestCase;

/**
 * This class can be extended to easily test admin pages.  It allows you to
 * dispatch pages from a component and test their validation logic or
 * their rendered output.
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
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
     * Get a component object, injecting the supplied request so that we can
     * test different responses.
     *
     * @param Request $request
     * @return \Dewdrop\Admin\ComponentAbstract
     */
    public function getComponent(Request $request)
    {
        $wpdb = new \wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
        $db   = new Adapter($wpdb);

        $paths = new Paths();
        $file  = $paths->getAdmin() . '/' . $this->componentFolder . '/Component.php';

        $className = 'Admin\\' . $this->componentNamespace . '\\Component';

        require_once $file;
        return new $className($db, $paths, $request);
    }

    /**
     * Set the folder where this component can be found.  For example, "my-component".
     *
     * @param string $componentFolder
     * @return \Dewdrop\Admin\TestCase
     */
    public function setComponentFolder($componentFolder)
    {
        $this->componentFolder = $componentFolder;

        return $this;
    }

    /**
     * Set the namespace of the component.
     *
     * @param string $componentNamespace
     * @return \Dewdrop\Admin\TestCase
     */
    public function setComponentNamespace($componentNamespace)
    {
        $this->componentNamespace = $componentNamespace;

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
        $method    = (count($post) ? 'POST' : 'GET');
        $request   = new Request($post, $query, $method);
        $component = $this->getComponent($request);

        $response = $this->getMock(
            '\Dewdrop\Admin\Response',
            array('render', 'executeHelper'),
            array()
        );

        $component->route($name, $response);

        return $response;
    }

    /**
     * Assert that the supplied CSS selector matches the supplied HTML.
     *
     * @param string $selector
     * @param string $html
     * @return void
     */
    public function assertMatchesDomQuery($selector, $html)
    {
        $query   = new DomQuery($html);
        $results = $query->execute($selector);

        $this->assertTrue(
            count($results) > 0,
            "The HTML output does not match the DOM query \"{$selector}\"."
        );
    }
}
