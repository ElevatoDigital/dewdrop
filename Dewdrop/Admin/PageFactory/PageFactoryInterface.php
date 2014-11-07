<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\PageFactory;

/**
 * Interface for admin page factories
 */
interface PageFactoryInterface
{
    /**
     * Returns a page instance for the given name or false on failure
     *
     * @param string $name
     * @return \Dewdrop\Admin\Page\PageAbstract|false
     */
    public function createPage($name);

    /**
     * List all the pages made available by the factory.  Should return an array
     * of \Dewdrop\Admin\PageFactory\PageFactoryInterface objects.
     *
     * @return array
     */
    public function listAvailablePages();
}
