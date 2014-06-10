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
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;
use Dewdrop\Fields\Helper\SelectSort;
use Dewdrop\Fields\Listing;
use Dewdrop\Fields\RowLinker;
use Dewdrop\Pimple as DewdropPimple;
use Pimple;

abstract class CrudAbstract extends Silex implements CrudInterface
{
    protected $selectSort;

    protected $visibilityFilter;

    protected $listing;

    protected $rowLinker;

    public function __construct(Pimple $pimple = null, $componentName = null)
    {
        $this->pimple = ($pimple ?: DewdropPimple::getInstance());

        $this->selectSort = new SelectSort($this->pimple['dewdrop-request']);

        $this->visibilityFilter = new VisibilityFilter(
            $this->getFullyQualifiedName(),
            $this->pimple['db']
        );

        $this->fields    = new Fields();
        $this->rowLinker = new RowLinker($this->fields, $this->pimple['dewdrop-request']);

        parent::__construct($pimple, $componentName);

        if (!$this->getPrimaryModel() instanceof AdminModelInterface) {
            throw new Exception(
                'When extending \Dewdrop\Admin\Component\Silex\Crud, your primary '
                . 'model must implement the \Dewdrop\Db\Table\AdminModelInterface.'
            );
        }

        $this->listing = new Listing($this->getPrimaryModel()->selectAdminListing());
        $this->listing->registerSelectModifier($this->getSelectSortHelper());

        $this->addPageFactory(new CrudFactory($this));
    }

    public function getSelectSortHelper()
    {
        return $this->selectSort;
    }

    public function getVisibilityFilter()
    {
        return $this->visibilityFilter;
    }

    public function getListing()
    {
        return $this->listing;
    }

    public function getPrimaryModel()
    {
        if (!$this->model instanceof DbTable) {
            throw new Exception(
                'Either implement your own getPrimaryModel() method or set your '
                . 'component\'s $model property during init().'
            );
        }

        return $this->model;
    }

    public function getRowLinker()
    {
        return $this->rowLinker;
    }

    public function getFields()
    {
        return $this->fields;
    }
}
