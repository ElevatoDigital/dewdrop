<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Admin\Page\PageAbstract;

/**
 * Get a URL for a page in the current admin component.
 */
class AdminUrl extends AbstractHelper
{
    /**
     * The page this helper's view is attached to.
     *
     * @param \Dewdrop\Admin\Page\PageAbstract
     */
    private $page;

    /**
     * Use the supplied page and query string parameters to get a
     * URL for a page in the current admin component.
     *
     * @see PageAbstract::url()
     * @param string $page
     * @param array $params
     * @return string
     */
    public function direct($page, array $params = array())
    {
        return $this->page->url($page, $params);
    }

    /**
     * Set the page this helper's view is attached to.
     *
     * @param PageAbstract $page
     * @return \Dewdrop\View\Helper\AdminUrl
     */
    public function setPage(PageAbstract $page)
    {
        $this->page = $page;

        return $this;
    }
}
