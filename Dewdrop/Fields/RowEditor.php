<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Db\Row;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\InputFilter as InputFilterHelper;
use Dewdrop\Fields\RowEditor\Link\LinkInterface;
use Dewdrop\Fields\RowEditor\Link\Field as FieldLink;
use Dewdrop\Fields\RowEditor\Link\QueryString as QueryStringLink;
use Dewdrop\Request;

/**
 * This class assists in the editing of one or more row objects.  To achieve
 * this, it first helps you associate row objects with all the DB fields in your
 * \Dewdrop\Fields object.  When you initially add fields to a \Dewdrop\Fields
 * object, it is usually via a \Dewdrop\Db\Table model rather than a row.  So,
 * to edit those fields, you somehow have to call setRow() on each of those
 * fields, giving them the ability to get and set values on that row.
 */
class RowEditor
{
    /**
     * An associative array containing all the row objects you've provided, with
     * the model names from your \Dewdrop\Fields object as keys.
     *
     * @var array
     */
    private $rowsByName = array();

    /**
     * Validation errors captured during a call to isValid().
     *
     * @var array
     */
    private $errors = array();

    /**
     * In most cases, you can link all your rows simply by pointing RowEditor
     * to a variable it can use to find() the row by primary key.
     *
     * @see \Dewdrop\Fields\RowEditor::linkByQueryString()
     * @see \Dewdrop\Fields\RowEditor::linkByField()
     * @var array
     */
    private $links = array();

    /**
     * Set a custom link callback to use when linking rows to their fields.
     *
     * @var callable
     */
    private $linkCallback;

    /**
     * Set a custom callback to use when saving.
     *
     * @var callable
     */
    private $saveCallback;

    /**
     * A ZF2 \Zend\InputFilter\InputFilter object containing inputs for
     * editable fields.
     *
     * @var \Zend\InputFilter\InputFilter
     */
    private $inputFilter;

    /**
     * A helper that will generate \Zend\InpuFilter\Input objects for each
     * editable field.
     *
     * @var \Dewdrop\Fields\Helper\InputFilter
     */
    private $inputFilterHelper;

    /**
     * Supply the fields and HTTP request that will be used during editing.
     *
     * @param Fields $fields
     * @param Request $request
     * @param InputFilterHelper $inputFilterHelper
     */
    public function __construct(Fields $fields, Request $request, InputFilterHelper $inputFilterHelper = null)
    {
        $this->fields            = $fields;
        $this->request           = $request;
        $this->inputFilterHelper = ($inputFilterHelper ?: new InputFilterHelper());
    }

    /**
     * Just a shortcut/alias for linkRowsToFields().  Easier to remember.
     *
     * @return RowEditor
     */
    public function link()
    {
        return $this->linkRowsToFields();
    }

    /**
     * Create row objects and link them to the appropriate database fields.  To
     * create the row objects, you can either provide a number of
     * \Dewdrop\Fields\RowEditor\Link\LinkInterface objects (typically by calling
     * this class's linkByQueryString() and linkByField() methods), or you can
     * provide your own custom linker by calling setLinkCallback().
     *
     * @return RowEditor
     */
    public function linkRowsToFields()
    {
        if ($this->linkCallback) {
            call_user_func($this->linkCallback, $this, $this->request);
        } else {
            foreach ($this->links as $modelName => $link) {
                $this->setRow(
                    $modelName,
                    $link->link($this->getModel($modelName))
                );
            }
        }

        return $this;
    }

    /**
     * Set a custom callback to be used when doing row linking.  This can be
     * useful if your case is not covered by the built-in linkers available
     * via linkByQueryString() and linkByField().
     *
     * Your callback will receive two arguments:
     *
     * 1. The RowEditor instance.
     *
     * 2. The HTTP request object.
     *
     * @param callable $linkCallback
     * @return RowEditor
     */
    public function setLinkCallback(callable $linkCallback)
    {
        $this->linkCallback = $linkCallback;

        return $this;
    }

    /**
     * Add a custom LinkInterface object.
     *
     * @param string $modelName
     * @param LinkInterface $link
     * @return RowEditor
     */
    public function addLink($modelName, LinkInterface $link)
    {
        $this->links[$modelName] = $link;

        return $this;
    }

    /**
     * Link a row using a query string variable.
     *
     * @see \Dewdrop\Fields\RowEditor\Link\QueryString
     * @param string $modelName
     * @param string $queryStringVariableName
     * @return RowEditor
     */
    public function linkByQueryString($modelName, $queryStringVariableName)
    {
        return $this->addLink($modelName, new QueryStringLink($this->request, $queryStringVariableName));
    }

    /**
     * Link a row using a field object.
     *
     * @see \Dewdrop\Fields\RowEditor\Link\QueryString
     * @param string $modelName
     * @param DbField $field
     * @return RowEditor
     */
    public function linkByField($modelName, DbField $field)
    {
        return $this->addLink($modelName, new FieldLink($field));
    }

    /**
     * Check to see if we're currently editing new rows or existing rows.
     * If any of the rows are not new, this will return false.
     *
     * @return boolean
     */
    public function isNew()
    {
        $isNew = true;

        foreach ($this->rowsByName as $row) {
            if (!$row->isNew()) {
                $isNew = false;
            }
        }

        return $isNew;
    }

    /**
     * Assign the supplied data to our field objects and check to see if it
     * is valid using the internal input filter.
     *
     * @param array $data
     * @return boolean
     */
    public function isValid(array $data)
    {
        foreach ($this->fields->getEditableFields() as $field) {
            if (isset($data[$field->getId()])) {
                $field->setValue($data[$field->getControlName()]);
            } elseif ($field instanceof DbField && $field->isType('boolean')) {
                /**
                 * Checkboxes are omitted from POST completely when not checked, so this
                 * branch accommodates that by setting them to false, if they are missing
                 * from the data.
                 */
                $field->setValue(0);
            }
        }

        $this->getInputFilter()->setData($data);

        return $this->getInputFilter()->isValid();
    }

    /**
     * Get the \Zend\InputFilter\InputFilter object from the helper.  At the time
     * this method is first called, the helper will be asked to add
     * \Zend\InputFilter\Input objects from each field to the filter.
     *
     * @return \Zend\InputFilter\InputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            foreach ($this->fields->getEditableFields() as $field) {
                $this->inputFilterHelper->addInput($field);
            }

            $this->inputFilter = $this->inputFilterHelper->getInputFilter();
        }

        return $this->inputFilter;
    }

    /**
     * Set a custom save callback.  This can be useful if you need to do
     * anything beyond calling the save methods on your rows.  Your callback
     * will receive this object as its only argument.  You can call getRow()
     * as needed to retrieve your rows for saving.
     *
     * @param callable $callback
     * @return RowEditor
     */
    public function setSaveCallback(callable $callback)
    {
        $this->saveCallback = $callback;

        return $this;
    }

    /**
     * Save the editor's rows.  You can optionally provide your own save
     * routine by calling setSaveCallback().
     *
     * @return RowEditor
     */
    public function save()
    {
        if (is_callable($this->saveCallback)) {
            call_user_func($this->saveCallback, $this);
        } else {
            foreach ($this->rowsByName as $row) {
                $row->save();
            }
        }

        return $this;
    }

    /**
     * Assign the provided row object to the named model.  This will iterate
     * over all the fields and call setRow() on each one associated with
     * the named model so that their values can be set and retrieved for
     * editing.
     *
     * @throws \Dewdrop\Fields\Exception
     * @param string $modelName
     * @param Row $row
     * @return RowEditor
     */
    public function setRow($modelName, Row $row)
    {
        $model = $this->getModel($modelName);

        if ($model !== $row->getTable()) {
            throw new Exception('The row should be from the same table instance.');
        }

        foreach ($this->fields as $field) {
            if ($field instanceof DbField && $field->getTable() === $model) {
                $field->setRow($row);
            }
        }

        $this->rowsByName[$modelName] = $row;

        return $this;
    }

    /**
     * Get a row by its model name.
     *
     * @param string $modelName
     * @return Row
     */
    public function getRow($modelName)
    {
        return $this->rowsByName[$modelName];
    }

    /**
     * Get a model from the fields object by its model name.
     *
     * @throws Dewdrop\Fields\Exception
     * @param string $modelName
     * @return \Dewdrop\Db\Table
     */
    public function getModel($modelName)
    {
        $models = $this->fields->getModelsByName();

        if (!isset($models[$modelName])) {
            throw new Exception("Could not find model with name '{$modelName}'");
        }

        return $models[$modelName];
    }
}
