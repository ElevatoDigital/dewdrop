<?php

namespace Dewdrop\Admin\Component;

use Dewdrop\Admin\PageFactory\Crud as CrudFactory;
use Dewdrop\Fields;
use Dewdrop\Fields\Filter\Groups as GroupsFilter;
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;
use Dewdrop\Fields\Helper\SelectPaginate;
use Dewdrop\Fields\Helper\SelectSort;
use Dewdrop\Fields\RowEditor;
use Pimple;

abstract class CrudAbstract extends ComponentAbstract implements CrudInterface
{
    /**
     * @var SelectPaginate
     */
    protected $selectPaginate;

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

    /**
     * @return SelectPaginate
     */
    public function getSelectPaginateHelper()
    {
        if (null === $this->selectPaginate) {
            $this->selectPaginate = new SelectPaginate($this->getRequest());
        }

        return $this->selectPaginate;
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

    public function getRowEditor()
    {
        if (!$this->rowEditor) {
            $this->rowEditor = new RowEditor($this->getFields(), $this->getRequest());
        }

        return $this->rowEditor;
    }

    /**
     * @param SelectPaginate $selectPaginate
     * @return CrudAbstract
     */
    public function setSelectPaginateHelper(SelectPaginate $selectPaginate)
    {
        $this->selectPaginate = $selectPaginate;

        return $this;
    }
}
