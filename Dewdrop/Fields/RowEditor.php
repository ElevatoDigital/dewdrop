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
use Dewdrop\Db\Table;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\InputFilter as InputFilterHelper;
use Dewdrop\Fields\Helper\SaveHandler as SaveHandlerHelper;
use Dewdrop\Fields\RowEditor\Link\LinkInterface;
use Dewdrop\Fields\RowEditor\Link\Field as FieldLink;
use Dewdrop\Fields\RowEditor\Link\QueryString as QueryStringLink;
use Dewdrop\Request;
use Dewdrop\SaveHandlerInterface;

/**
 * This class assists in the editing of one or more row objects.  To achieve
 * this, it first helps you associate row objects with all the DB fields in your
 * \Dewdrop\Fields object.  When you initially add fields to a \Dewdrop\Fields
 * object, it is usually via a \Dewdrop\Db\Table model rather than a row.  So,
 * to edit those fields, you somehow have to call setRow() on each of those
 * fields, giving them the ability to get and set values on that row.
 */
class RowEditor implements SaveHandlerInterface
{
    /**
     * An associative array containing all the row objects you've provided, with
     * the model names from your \Dewdrop\Fields object as keys.
     *
     * @var array
     */
    private $rowsByName = array();

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
     * @var \Dewdrop\Fields\Helper\SaveHandler
     */
    private $saveHandlerHelper;

    /**
     * A boolean field that can be used to flag a row as deleted.
     *
     * @var DbField
     */
    private $deleteField;

    /**
     * The fields from which we'll get input filter inputs and for which we'll
     * set values.
     *
     * @var Fields
     */
    private $fields;

    /**
     * The HTTP request where we'll get the input data.
     *
     * @var Request
     */
    private $request;

    /**
     * Default values for rows by model name.  Will set these automatically
     * once the rows are linked.
     *
     * @var array
     */
    private $defaultsByModelName = [];

    /**
     * Supply the fields and HTTP request that will be used during editing.
     *
     * @param Fields $fields
     * @param Request $request
     * @param InputFilterHelper $inputFilterHelper
     */
    public function __construct(
        Fields $fields,
        Request $request,
        InputFilterHelper $inputFilterHelper = null,
        SaveHandlerHelper $saveHandlerHelper = null
    ) {
        $this->fields            = $fields;
        $this->request           = $request;
        $this->inputFilterHelper = ($inputFilterHelper ?: new InputFilterHelper());
        $this->saveHandlerHelper = ($inputFilterHelper ?: new SaveHandlerHelper());
    }

    /**
     * Get the Fields object associated with this RowEditor.
     *
     * @return Fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get the HTTP request associated wit this RowEditor.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
    /**
     * Set some default values for the row matching the supplied model name.
     * Once the rows are linked, your defaults will be applied automatically.
     *
     * @param string $modelName
     * @param array $defaults
     * @return $this
     * @throws Exception
     */
    public function setDefaults($modelName, array $defaults)
    {
        $this->defaultsByModelName[$modelName] = $defaults;

        if ($this instanceof RowEditor && $this->hasRow($modelName)) {
            $this->applyDefaultsToRow($this->getRow($modelName), $defaults);
        }

        return $this;
    }

    /**
     * Apply the supplied default values to the row, skipping any fields
     * that already have a value.
     *
     * @param Row $row
     * @param array $defaults
     * @return $this
     * @throws \Dewdrop\Exception
     */
    public function applyDefaultsToRow(Row $row, array $defaults)
    {
        foreach ($defaults as $key => $value) {
            if (!$row->get($key)) {
                if ($value instanceof FieldInterface) {
                    $value = $value->getValue();
                }

                $row->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * Clear out the row objects on this editor.  Useful if you're using the same editor
     * @return $this
     */
    public function reset()
    {
        $this->rowsByName = [];

        return $this;
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
     * @throws \Dewdrop\Exception
     * @return RowEditor
     */
    public function linkRowsToFields()
    {
        if ($this->linkCallback) {
            call_user_func($this->linkCallback, $this, $this->request);
        } else {
            if (!count($this->links)) {
                throw new Exception('Cannot link rows without Link objects or link callback defined.');
            }

            /* @var $link LinkInterface */
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
     * A shortcut for linkByQueryString() that uses Table object get the model
     * and query string param name.
     *
     * @param Table $table
     * @return RowEditor
     * @throws Exception
     */
    public function linkTableByQueryString(Table $table)
    {
        $primaryKey = $table->getPrimaryKey();

        if (1 !== count($primaryKey)) {
            throw new Exception('Can only use linkTableByQueryString() when a single primary key column is present.');
        }

        return $this->linkByQueryString(
            $table->getTableName(),
            current($primaryKey)
        );
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
        return $this->addLink($modelName, new FieldLink($this, $field));
    }

    /**
     * Check to see if we're currently editing new rows or existing rows.
     * If any of the rows are not new, this will return false.
     *
     * @throws \Dewdrop\Exception
     * @return boolean
     */
    public function isNew()
    {
        if (!count($this->rowsByName)) {
            throw new Exception('You cannot call isNew() prior to calling link(), which creates the row objects.');
        }

        $isNew = true;

        /* @var $row Row */
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
        $inputFilter = $this->getInputFilter();

        /* @var $field DbField */
        foreach ($this->fields->getEditableFields() as $field) {
            $presentInData = array_key_exists($field->getId(), $data);

            if ($presentInData) {
                /* @var $filter \Zend\Filter\FilterChain */
                $filter = $inputFilter->get($field->getId())->getFilterChain();
                $value  = $data[$field->getId()];

                if (!$value && $field instanceof DbField && $field->isType('reference')) {
                    $value = null;
                }

                $field->setValue($filter->filter($value));
            } elseif ($field instanceof DbField && $field->isType('boolean')) {
                /**
                 * Checkboxes are omitted from POST completely when not checked, so this
                 * branch accommodates that by setting them to false, if they are missing
                 * from the data.
                 */
                $field->setValue(0);
            } elseif ($field instanceof DbField && $field->isType('manytomany')) {
                /**
                 * A similar special case for empty checkbox lists in manytomany fields.
                 */
                $field->setValue([]);
            }

            // If field was not present in user-supplied data, use the field's current value for validation purposes
            if (!$presentInData) {
                $data[$field->getId()] = $field->getValue();
            }
        }

        $inputFilter->setData($data);

        return $inputFilter->isValid();
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
     * Get the validation messages associated with a specific field.
     *
     * @param FieldInterface $field
     * @return array
     */
    public function getMessages(FieldInterface $field)
    {
        return $this->getInputFilter()->get($field->getId())->getMessages();
    }

    /**
     * Set the field that can be used to delete this item.
     *
     * @param DbField $deleteField
     * @return $this
     */
    public function setDeleteField(DbField $deleteField)
    {
        $this->deleteField = $deleteField;

        return $this;
    }

    /**
     * Check to see if a delete field is assigned to this editor.
     *
     * @return boolean
     */
    public function hasDeleteField()
    {
        return null !== $this->deleteField;
    }

    /**
     * Perform a delete using the RowEditor's delete field, if specified.
     *
     * @todo Add support for "active" fields as well.
     *
     * @return void
     */
    public function delete()
    {
        if ($this->hasDeleteField()) {
            $this->deleteField
                ->setValue(1)
                ->getRow()
                    ->save();
        }
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
            $this->saveRowsByLinks($this->rowsByName);
        }

        return $this;
    }

    /**
     * In most common cases, the rows handled by a RowEditor object are linked
     * to one another by foreign key using the linkByField() method.  When this
     * is the case, we can traverse those links in reverse order, saving the
     * rows at the end of the chain of links first so that they can populate
     * the fields on the tables farther up the chain.  Once the field links
     * have been traversed, we can save the other rows safely.
     *
     * If you are not using linkByField(), you may need to provide a custom
     * save callback that takes your situation into account.
     *
     * @return RowEditor
     */
    public function saveRowsByLinks()
    {
        $reverseLinks = array_reverse($this->links, true);
        $savedRows    = array();

        // First save Field links in the chain in reverse order
        foreach ($reverseLinks as $modelName => $link) {
            if ($link instanceof FieldLink) {
                $row = $this->getRow($modelName);

                $row->save();

                $link->populateValueFromSavedRow($row);

                $savedRows[] = $modelName;
            }
        }

        // Now save any rows that weren't saved in the first loop

        /* @var $row Row */
        foreach ($this->rowsByName as $modelName => $row) {
            if (!in_array($modelName, $savedRows)) {
                $row->save();
            }
        }

        /* @var $field FieldInterface */
        foreach ($this->fields->getEditableFields() as $field) {
            $this->saveHandlerHelper->save($field);
        }

        return $this;
    }

    /**
     * Check to see if the row editor has a row with the given model name.
     *
     * @param string $modelName
     * @return bool
     */
    public function hasRow($modelName)
    {
        return array_key_exists($modelName, $this->rowsByName);
    }

    public function setRowByData($modelName, array $data)
    {
        $model = $this->getModel($modelName);

        $pkeyColumns = $model->getPrimaryKey();
        $pkeyValues  = [];

        foreach ($pkeyColumns as $column) {
            if (isset($data[$column]) && $data[$column]) {
                $pkeyValues[$column] = $data[$column];
            }
        }

        if (!count($pkeyValues)) {
            return $this->setRow($modelName, $model->createRow());
        } else {
            return $this->setRow($modelName, call_user_func_array([$model, 'find'], $pkeyValues));
        }
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

        if ($this->deleteField) {
            $this->deleteField->setRow($row);
        }

        if (array_key_exists($modelName, $this->defaultsByModelName)) {
            $this->applyDefaultsToRow($row, $this->defaultsByModelName[$modelName]);
        }

        $this->rowsByName[$modelName] = $row;

        return $this;
    }

    /**
     * Get a row by its model name.
     *
     * @param string $modelName
     * @throws \Dewdrop\Exception
     * @return Row
     */
    public function getRow($modelName)
    {
        if (!isset($this->rowsByName[$modelName])) {
            $models = $this->fields->getModelsByName();

            if (!isset($models[$modelName])) {
                throw new Exception("Attempting to retrieve row for unknown model: {$modelName}");
            } else {
                throw new Exception('Attempting to retrieve row prior to linking');
            }
        }

        return $this->rowsByName[$modelName];
    }

    /**
     * Get all the row objects from this editor.
     *
     * @return array
     */
    public function getRows()
    {
        return $this->rowsByName;
    }

    /**
     * Get a model from the fields object by its model name.
     *
     * @throws Exception
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
