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
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;

/**
 * Generate a CSV export for a CRUD component's Listing.
 */
class Export extends StockPageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * @var VisibilityFilter
     */
    protected $visibilityFilter;

    /**
     * @var bool
     */
    protected $enableVisibilityFilter = true;

    /**
     * @param VisibilityFilter $visibilityFilter
     * @return $this
     */
    public function setVisibilityFilter(VisibilityFilter $visibilityFilter)
    {
        $this->visibilityFilter = $visibilityFilter;

        return $this;
    }

    /**
     * @return VisibilityFilter
     */
    public function getVisibilityFilter()
    {
        if (!$this->visibilityFilter) {
            $this->visibilityFilter = $this->component->getVisibilityFilter();
        }

        return $this->visibilityFilter;
    }

    /**
     * @return $this
     */
    public function disableVisibilityFilter()
    {
        $this->enableVisibilityFilter = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableVisibilityFilter()
    {
        $this->enableVisibilityFilter = true;

        return $this;
    }

    /**
     * Ensure permissions are correctly set and then pass the component
     * along to the view.
     */
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('export');

        $filters = [];

        if ($this->enableVisibilityFilter) {
            $filters[] = $this->getVisibilityFilter();
        }

        $filters[] = $this->component->getFieldGroupsFilter();

        $this->view
            ->assign('component', $this->component)
            ->assign('filters', $filters);

        return $this->renderView();
    }
}
