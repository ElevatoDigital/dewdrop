<?php

namespace Dewdrop\Admin\Component;

use Dewdrop\Admin\PageFactory\Crud as CrudFactory;
use Dewdrop\Fields;
use Dewdrop\Fields\Filter\Groups as GroupsFilter;
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;
use Dewdrop\Fields\RowEditor;
use Pimple;

abstract class CrudAbstract extends ComponentAbstract implements CrudInterface
{
    protected $fieldGroupsFilter;

    protected $visibilityFilter;

    protected $listing;

    protected $rowEditor;

    public function __construct(Pimple $pimple = null, $componentName = null)
    {
        parent::__construct($pimple, $componentName);

        $this->addPageFactory(new CrudFactory($this));
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
}
