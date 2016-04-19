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
class AdminUrl extends AbstractHelper implements PageDelegateInterface
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
     * @param boolean $resetParams
     * @return string
     */
    public function direct($page = null, array $params = array(), $resetParams = true)
    {
        if (!$page) {
            return $this;
        }

        if (!$resetParams) {
            $params = array_merge($this->view->getRequest()->getQuery(), $params);
        }

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

    /**
     * Return the page this helper users to geneate URLs so that partials
     * can function properly.
     *
     * @return PageAbstract
     */
    public function getPage()
    {
        return $this->page;
    }
}
