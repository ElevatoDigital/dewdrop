<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Test\Admin;

use Dewdrop\Test\BaseTestCase;

/**
 * A basic admin test case with the ability to easily dispatch admin
 * pages and take advantage of utility methods in the base Dewdrop
 * test case class.
 */
abstract class AdminBaseTestCase extends BaseTestCase implements AdminInterface
{
    /**
     * Initialize the admin helper for this test case.  This should be
     * called in your test case's setUp() method so that you can dispatch
     * pages from your component for testing.
     *
     * @param string $componentFolder
     * @param string $componentNamespace
     * @return Dewdrop\Test\Admin\AdminInterface
     */
    public function initHelper($componentFolder, $componentNamespace)
    {
        $this->helper = new Helper($this, $componentFolder, $componentNamespace);
    }

    /**
     * Dispatch the named page with the POST and GET values supplied to
     * the request object.  The response object will be returned so that
     * you can examine the output, etc.
     *
     * @param string $name
     * @param array $post
     * @param array $query
     * @return \Dewdrop\Admin\Response
     */
    public function dispatchPage($name, array $post = array(), array $query = array())
    {
        return $this->helper->dispatchPage($name, $post, $query);
    }

    /**
     * Get a component object.  This can be useful if you don't want to
     * execute the full page dispatch process but instead want to interact
     * with the component object directly or run selected portions of a
     * page's functionality after routing.
     *
     * @param Request $request
     * @return \Dewdrop\Admin\ComponentAbstract
     */
    public function getComponent(Request $request)
    {
        return $this->helper->getComponent($request);
    }
}
