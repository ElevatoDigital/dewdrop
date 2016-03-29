<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;

/**
 * This page, typically rendered in a modal rather than as an independent
 * page (though it works in that context, too) renders a "detail view" for
 * a single record from a listing.  It actually using the Listing object to
 * retrieve its data, so if the Listing/table works, this should work fine,
 * too.  It just shows _all_ available visible fields rather than only those
 * that pass the component's visibility filter.
 */
class View extends StockPageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * Ensure the user has permission to view record details on this component.
     */
    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('view');
    }

    /**
     * Pass a bunch of dependencies to the View.
     */
    public function render()
    {
        $listing = $this->component->getListing();
        $id      = $this->request->getQuery($listing->getPrimaryKey()->getName());
        $fields  = $this->component->getFields()->getVisibleFields();
        $data    = $this->component->getListing()->fetchRow($fields, $id);

        $primaryKey = $this->component->getPrimaryModel()->getPrimaryKey();
        $params     = array();

        foreach ($primaryKey as $field) {
            $params[$field] = $this->request->getQuery($field);
        }

        $this->view->assign([
            'params'         => $params,
            'fields'         => $fields,
            'singularTitle'  => $this->component->getPrimaryModel()->getSingularTitle(),
            'data'           => $data,
            'id'             => $id,
            'groupingFilter' => $this->component->getFieldGroupsFilter(),
            'permissions'    => $this->component->getPermissions(),
            'isAjax'         => $this->request->isAjax()
        ]);

        // When requested over XHR, turn off the layout (admin shell chrome)
        if ($this->request->isAjax()) {
            $this->component->setShouldRenderLayout(false);
        }

        return $this->renderView();
    }
}
