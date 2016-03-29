<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Fields\Listing\Counter;

/**
 * This page leverages the \Dewdrop\Fields\Listing\Counter class to enable the user
 * to group all the records in a CRUD component by a field and count the number of
 * records assigned to each value for that field.  For example, if you had a "shirt
 * size" field, this page would render a row for each size along with the number
 * of people who had selected that size.
 */
class Counts extends StockPageAbstract
{
    /**
     * @var \Dewdrop\Admin\Component\CrudInterface|\Dewdrop\Admin\Component\ComponentAbstract
     */
    protected $component;

    /**
     * Ensure the user has permission to access the counts page on this component.
     */
    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('count-fields');
    }

    /**
     * Assemble elements from the component and a Counter object and pass them along
     * to the view.
     */
    public function render()
    {
        $fields   = $this->component->getFields();
        $selected = null;
        $counter  = null;
        $renderer = $this->view->tableCellRenderer();

        if ($this->request->getQuery('group_field')) {
            $selected = $fields->getVisibleFields()->getByQueryStringId($this->request->getQuery('group_field'));
            $counter  = new Counter($selected, $fields, $this->component->getListing());
        }

        $this->view->assign(
            [
                'title'         => $this->component->getTitle(),
                'model'         => $this->component->getPrimaryModel(),
                'fields'        => $this->component->getFields(),
                'listing'       => $this->component->getListing(),
                'renderer'      => $renderer,
                'selectedField' => ($selected ? $selected->getQueryStringId() : null),
                'countFields'   => ($counter ? $counter->buildRenderFields() : null),
                'data'          => ($selected ? $counter->fetchData($renderer) : []),
                'request'       => $this->request
            ]
        );

        return $this->renderView();
    }
}
