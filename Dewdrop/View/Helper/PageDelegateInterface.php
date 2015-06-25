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
 * Some helpers depend upon a page object to provide their functionality.
 * In Dewdrop core, the AdminUrl helper is the prime example of this.
 * This interface lets \Dewdrop\View\View know that is the case so that
 * it can pass along the page object when creating partials, etc.
 */
interface PageDelegateInterface
{
    /**
     * Assign a page object the helper can use.
     *
     * @param PageAbstract $page
     * @return PageDelegateInterface
     */
    public function setPage(PageAbstract $page);

    /**
     * Get the page this helper uses to complete its task.
     *
     * @return PageAbstract
     */
    public function getPage();
}
