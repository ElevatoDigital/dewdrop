<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component;

use Dewdrop\ActivityLog\Handler\NullHandler as ActivityLogNullHandler;
use Dewdrop\Admin\PageFactory\Crud as CrudFactory;
use Dewdrop\Fields;
use Dewdrop\Fields\Filter\Groups as GroupsFilter;
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;
use Dewdrop\Fields\Listing;
use Dewdrop\Fields\RowEditor;
use Pimple;

/**
 * CrudAbstract basically provides some shortcuts to implementing the
 * CrudInterface used for all the stock CRUD functionality in the Dewdrop
 * admin.  It's a little fiddly, really.  There are several cases where you
 * might find yourself calling parent::someMethod() and then modifying the
 * bit that was provided by CrudAbstract.  But it does save some boilerplate
 * if you're cranking out lots of CRUD components.
 */
abstract class CrudAbstract extends ComponentAbstract implements CrudInterface
{
    /**
     * A filter that can be used to sort and group fields according to
     * user-defined selections.
     *
     * @var GroupsFilter
     */
    protected $fieldGroupsFilter;

    /**
     * A filter to determine which fields should be displayed in a Component's
     * Listing.
     *
     * @var VisibilityFilter
     */
    protected $visibilityFilter;

    /**
     * The Listing used by the component.
     *
     * @var Listing
     */
    protected $listing;

    /**
     * The RowEditor used to save changes back to row objects.
     *
     * @var RowEditor
     */
    protected $rowEditor;

    /**
     * Add a Crud page factory to the component so that all the stock CRUD
     * pages are available.
     *
     * @param Pimple $pimple
     * @param null $componentName
     */
    public function __construct(Pimple $pimple = null)
    {
        parent::__construct($pimple);

        $this->addPageFactory(new CrudFactory($this));
    }

    /**
     * @return \Dewdrop\ActivityLog\Handler\TableHandler
     */
    public function getActivityLogHandler()
    {
        if (parent::getActivityLogHandler() instanceof ActivityLogNullHandler) {
            return $this->getPrimaryModel()->getActivityLogHandler();
        } else {
            return parent::getActivityLogHandler();
        }
    }

    /**
     * Get the sorting/grouping fields filter.
     *
     * @return GroupsFilter
     */
    public function getFieldGroupsFilter()
    {
        if (!$this->fieldGroupsFilter) {
            $this->fieldGroupsFilter = new GroupsFilter(
                $this->getFullyQualifiedName(),
                $this->getDb()
            );
        }

        return $this->fieldGroupsFilter;
    }

    /**
     * Get the visibility filter for listing fields.
     *
     * @return VisibilityFilter
     */
    public function getVisibilityFilter()
    {
        if (!$this->visibilityFilter) {
            $this->visibilityFilter = new VisibilityFilter(
                $this->getFullyQualifiedName(),
                $this->getDb()
            );
        }

        return $this->visibilityFilter;
    }

    /**
     * Get the row editor for saving changes to records in the component.  You'll
     * need to tell the row editor how to link rows for your component.
     *
     * @return RowEditor
     */
    public function getRowEditor()
    {
        if (!$this->rowEditor) {
            $this->rowEditor = new RowEditor($this->getFields(), $this->getRequest());
        }

        return $this->rowEditor;
    }
}
