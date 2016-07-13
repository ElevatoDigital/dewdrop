<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Fields\Listing\Counter;

/**
 * This page takes the selected field from the Counts page and uses it to fetch
 * data from a Dewdrop\Fields\Listing\Counter object and export it to CSV.
 */
class CountsExport extends StockPageAbstract
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
        $selected = $fields->getVisibleFields()->getByQueryStringId($this->request->getQuery('group_field'));
        $counter  = new Counter($selected, $fields, $this->component->getListing());
        $renderer = $this->view->csvCellRenderer();

        $this->component->setShouldRenderLayout(false);

        $this->view->assign(
            [
                'title'    => $this->component->getTitle(),
                'selected' => $selected,
                'fields'   => $counter->buildRenderFields(),
                'data'     => $counter->fetchData($renderer),
                'renderer' => $renderer,
            ]
        );

        return $this->renderView();
    }
}
