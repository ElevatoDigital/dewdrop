<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page\Stock;

/**
 * This page provides a full listing of a component's available pages.  Pages
 * in Dewdrop admin components are provided by PageFactory objects.  One factory
 * can override a page from another factory by responding to the same page name.
 * If a page is overridden (for example, you provide an Index page file even
 * though the Crud page factory would also provide and Index), the superseded
 * page will be crossed-out.
 */
class DebugPages extends StockPageAbstract
{
    /**
     * Pass all the page factories for this component into the view.
     */
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('debug');

        $this->view->assign([
            'pageFactories' => $this->component->getPageFactories(),
        ]);

        return $this->renderView();
    }
}
