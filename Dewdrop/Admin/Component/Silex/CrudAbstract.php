<?php

namespace Dewdrop\Admin\Component\Silex;

use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\Component\Silex;
use Dewdrop\Admin\PageFactory\Crud as CrudFactory;
use Dewdrop\Admin\Silex as SilexAdmin;
use Dewdrop\Db\Table as DbTable;
use Dewdrop\Db\Table\AdminModelInterface;
use Dewdrop\Exception;
use Dewdrop\Fields;
use Dewdrop\Fields\Filter\Groups as GroupsFilter;
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;
use Dewdrop\Fields\Helper\SelectSort;
use Dewdrop\Fields\Listing;
use Dewdrop\Fields\RowEditor;
use Dewdrop\Pimple as DewdropPimple;
use Pimple;

abstract class CrudAbstract extends Silex implements CrudInterface
{
    protected $selectSort;

    protected $fieldGroupsFilter;

    protected $visibilityFilter;

    protected $listing;

    protected $rowEditor;

    public function __construct(Pimple $pimple = null, $componentName = null)
    {
        parent::__construct($pimple, $componentName);

        $this->addPageFactory(new CrudFactory($this));
    }

    public function getSelectSortHelper()
    {
        if (!$this->selectSort) {
            $this->selectSort = new SelectSort($this->getRequest());
        }

        return $this->selectSort;
    }

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

    public function getListing()
    {
        return $this->listing;
    }

    public function getRowEditor()
    {
        if (!$this->rowEditor) {
            $this->rowEditor = new RowEditor($this->getFields(), $this->getRequest());
        }

        return $this->rowEditor;
    }

    public function getFields()
    {
        return $this->fields;
    }
}
