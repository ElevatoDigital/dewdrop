<?php

namespace Dewdrop\Admin\Component;

use Dewdrop\Db\Field;
use Dewdrop\Db\Select;
use Dewdrop\Db\Table;
use Dewdrop\Fields\Helper\SelectDeletedRecords as DeletedRecordsModifier;
use Dewdrop\Exception;
use Dewdrop\Fields\Listing;

abstract class CrudSimplifiedAbstract extends CrudAbstract
{
    /**
     * @var Table
     */
    protected $model;

    /**
     * @var Field
     */
    protected $primaryKeyField;

    /**
     * @var Field
     */
    protected $deleteField;

    /**
     * @var Select
     */
    protected $listingSelect;

    /**
     * @return Table
     */
    abstract public function createPrimaryModel();

    /**
     * Get the primary model that is used by this component.  This model will
     * be used to provide page and button titles.  By default, its primary key
     * will also be used to filter the listing when needed (e.g. when viewing
     * a single item rather than the full listing).
     *
     * @return \Dewdrop\Db\Table
     */
    public function getPrimaryModel()
    {
        if (!$this->model) {
            $this->model = $this->createPrimaryModel();
        }

        return $this->model;
    }

    /**
     * Get a \Dewdrop\Fields\Listing object that allows the component to
     * retrieve records for viewing.  The Listing handles applying user sorts
     * and filters.
     *
     * @return \Dewdrop\Fields\Listing
     */
    public function getListing()
    {
        if (!$this->listing) {
            $this->listing = new Listing($this->getListingSelect(), $this->getPrimaryKeyField());

            if ($this instanceof SortableListingInterface) {
                $this->listing->getSelectSortModifier()->setDefaultField($this->getSortField());
            }

            if ($this->getDeleteField()) {
                $deletedRecordsModifier = new DeletedRecordsModifier($this->getRequest(), $this->getDeleteField());
                $this->listing->registerSelectModifier($deletedRecordsModifier);
            }
        }

        return $this->listing;
    }

    public function getRowEditor()
    {
        if (!$this->rowEditor) {
            $this->rowEditor = parent::getRowEditor();
            $this->rowEditor->linkTableByQueryString($this->getPrimaryModel());

            if ($this->getDeleteField()) {
                $this->rowEditor->setDeleteField($this->getDeleteField());
            }
        }

        return $this->rowEditor;
    }

    protected function setListingSelect(Select $listingSelect)
    {
        $this->listingSelect = $listingSelect;

        return $this;
    }

    protected function getListingSelect()
    {
        if (!$this->listingSelect) {
            $this->listingSelect = $this->getPrimaryModel()->selectAdminListing();
        }

        return $this->listingSelect;
    }

    protected function setPrimaryKeyField(Field $primaryKeyField)
    {
        $this->primaryKeyField = $primaryKeyField;

        return $this;
    }

    protected function getPrimaryKeyField()
    {
        if (!$this->primaryKeyField) {
            $model      = $this->getPrimaryModel();
            $primaryKey = $model->getPrimaryKey();

            if (1 !== count($primaryKey)) {
                throw new Exception(
                    'The primary model on a CrudInterface component should have a single column primary key.'
                );
            }

            $columnName = current($primaryKey);

            $this->primaryKeyField = $model->field($columnName);
        }

        return $this->primaryKeyField;
    }

    protected function setDeleteField(Field $deleteField)
    {
        $this->deleteField = $deleteField;

        return $this;
    }

    protected function getDeleteField()
    {
        if (!$this->deleteField) {
            $model   = $this->getPrimaryModel();
            $columns = $model->getMetadata('columns');

            if (array_key_exists('deleted', $columns)) {
                $this->deleteField = $model->field('deleted');
            }
        }

        return $this->deleteField;
    }
}
