<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Db\Table;
use Dewdrop\Fields;
use Dewdrop\Fields\RowCollectionEditor\FieldFactory;
use Dewdrop\Pimple;
use Dewdrop\Request;
use Dewdrop\SaveHandlerInterface;

/**
 * The RowCollectionEditor provides an API for editing a variable-sized collection
 * of items.  Each item in the collection will get its own Fields and RowEditor
 * objects, allowing you to leverage the validation/filtering and data handling
 * capabilities in RowEditor while not having to manually track dynamically created
 * rows.
 *
 * To do this, you'll supply RowCollectionEditor with three callbacks:
 *
 * 1) setFetchDataCallback()
 * 2) setFieldsCallback()
 * 3) setRowEditorCallback()
 */
class RowCollectionEditor implements SaveHandlerInterface
{
    /**
     * @const
     */
    const DATA_MODE_CONTROL_NAMES = 'controlnames';

    /**
     * @const
     */
    const DATA_MODE_COLUMN_NAMES = 'columnnames';

    /**
     * The ID for the collection.  Used in input names, etc.
     * @var string
     */
    private $id;

    /**
     * A default title.
     *
     * @var string
     */
    private $title = 'Row Collection';

    /**
     * A default title.
     *
     * @var string
     */
    private $pluralTitle = 'Rows';

    /**
     * A default title.
     *
     * @var string
     */
    private $singularTitle = 'Row';

    /**
     * A factory for creating a Field object enabling editing or viewing
     * or data in this collection.
     *
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * The RowEditor objects created by this collection.
     *
     * @var array
     */
    private $editors;

    /**
     * A Request object used to give this collection access to POST data.
     *
     * @var Request
     */
    private $request;

    /**
     * A callback used to build a Fields object for each RowEditor created
     * by this collection.
     *
     * @var callable
     */
    private $fieldsCallback;

    /**
     * A callback used to fetch an initial data set for this collection.
     *
     * @var callable
     */
    private $fetchDataCallback;

    /**
     * A callback used to configure RowEditors produced by this collection.
     *
     * @var callable
     */
    private $rowEditorCallback;

    /**
     * Supply an ID for this collection.
     *
     * @param string $id
     * @param Request $request
     */
    public function __construct($id, Request $request = null)
    {
        $this->id      = $id;
        $this->request = ($request ?: Pimple::getResource('dewdrop-request'));
    }

    /**
     * Supply an alternate factory for generating field objects for this collection.
     *
     * @param FieldFactory $fieldFactory
     * @return $this
     */
    public function setFieldFactory(FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;

        return $this;
    }

    /**
     * Get a field factory that can be used to build a Field object representing
     * this collection.
     *
     * @return FieldFactory
     */
    public function getFieldFactory()
    {
        if (!$this->fieldFactory) {
            $this->fieldFactory = new FieldFactory($this);
        }

        return $this->fieldFactory;
    }

    /**
     * Set the ID for this collection.
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the ID for this collection.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the titles for this collection using the titles from a Table object.
     *
     * @param Table $table
     * @return $this
     */
    public function setTitlesFromTable(Table $table)
    {
        $this->title         = $table->getPluralTitle();
        $this->singularTitle = $table->getSingularTitle();
        $this->pluralTitle   = $table->getPluralTitle();

        return $this;
    }

    /**
     * Set a title for this collection.
     *
     * @param string $singularTitle
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get a title for this collection.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set a plural title for this collection.
     *
     * @param string $pluralTitle
     * @return $this
     */
    public function setPluralTitle($pluralTitle)
    {
        $this->pluralTitle = $pluralTitle;

        return $this;
    }

    /**
     * Get a plural title for this collection.
     *
     * @return string
     */
    public function getPluralTitle()
    {
        return $this->pluralTitle;
    }

    /**
     * Set a singular title for this collection.
     *
     * @param string $singularTitle
     * @return $this
     */
    public function setSingularTitle($singularTitle)
    {
        $this->singularTitle = $singularTitle;

        return $this;
    }

    /**
     * Get a singular title for this collection.
     *
     * @return string
     */
    public function getSingularTitle()
    {
        return $this->singularTitle;
    }

    /**
     * Set the callback you'll use to configure each RowEditor created
     * by this collection.  You'll use this callback to set a delete
     * field, set defaults (e.g. to link the RowEditor back to parent
     * RowEditor this collection is attached to), link rows together
     * by their foreign keys, etc.
     *
     * @param callable $rowEditorCallback
     * @return $this
     */
    public function setRowEditorCallback(callable $rowEditorCallback)
    {
        $this->rowEditorCallback = $rowEditorCallback;

        return $this;
    }

    /**
     * Set the callback you'll use to build a Fields object for each
     * RowEditor created by this collection.  Your fields callback
     * will receive the empty Fields objects as its first and only
     * argument, so you don't have to instantiate or return it
     * yourself.  Not that you'll want to use new Table objects to
     * get your Field objects each time this callback is called to
     * ensure each RowEditor operates independently from the others.
     *
     * @param callable $fieldsCallback
     * @return $this
     */
    public function setFieldsCallback(callable $fieldsCallback)
    {
        $this->fieldsCallback = $fieldsCallback;

        return $this;
    }

    /**
     * Set the callback you'll use to fetch the initial data set for the
     * collection.
     *
     * @param callable $fetchDataCallback
     * @return $this
     */
    public function setFetchDataCallback(callable $fetchDataCallback)
    {
        $this->fetchDataCallback = $fetchDataCallback;

        return $this;
    }

    /**
     * Check to see if all editors associated with this collection are valid.
     *
     * @return bool
     */
    public function isValid()
    {
        $isValid = true;
        $data    = $this->getData(RowCollectionEditor::DATA_MODE_CONTROL_NAMES);

        /* @var $editor \Dewdrop\Fields\RowEditor */
        foreach ($this->getEditors() as $index => $editor) {
            $editorData = (isset($data[$index]) ? $data[$index] : []);

            if (!$editor->isValid($editorData)) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    /**
     * Save all editors.  We assume that you've already used isValid() to
     * populate all editors with the request data and ensure they're valid
     * prior to calling save().  We also check to see if any editors are
     * queued for deletion at this point and will call their delete() method
     * rather than save() if that's the case.
     *
     * @return bool
     */
    public function save()
    {
        $queuedToDelete = $this->request->getPost($this->id . ':queued_to_delete');

        /* @var $editor RowEditor */
        foreach ($this->getEditors() as $index => $editor) {
            if (isset($queuedToDelete[$index]) && $queuedToDelete[$index] && $editor->hasDeleteField()) {
                $editor->delete();
            } else {
                $editor->save();
            }
        }

        return true;
    }

    /**
     * Fetch data for this collection.  If your request is a POST, then we'll
     * get the data from the request.  If it's not a POST, we ask the
     * fetchDataCallback for the initial data set.
     *
     * @param string $mode
     * @return array|mixed
     */
    public function getData($mode = self::DATA_MODE_COLUMN_NAMES)
    {
        if (!$this->request->isPost()) {
            return call_user_func($this->fetchDataCallback);
        } else {
            return $this->getDataFromRequest($this->request->getPost(), $mode);
        }
    }

    /**
     * Get a Fields object for this collection not associated with any
     * RowEditor.  Useful for rendering a table displaying all the
     * collection's data, for example.
     *
     * @return Fields
     */
    public function getFields()
    {
        $fields = new Fields();
        call_user_func($this->fieldsCallback, $fields);
        return $fields;
    }

    /**
     * Reset editors.  Useful if your data changes and you want to build
     * all the editors again to reflect that.
     *
     * @return $this
     */
    public function resetEditors()
    {
        $this->editors = null;

        return $this;
    }


    /**
     * Get all data-based (rather than blank) editors from this collection.
     *
     * @return array
     */
    public function getEditors()
    {
        if (!$this->editors) {
            $this->editors = [];

            foreach ($this->getData() as $data) {
                $this->editors[] = $this->instantiateEditor($data);
            }
        }

        return $this->editors;
    }

    /**
     * Get a blank RowEditor instance with no data associated with it.
     *
     * @return RowEditor
     */
    public function getBlankEditor()
    {
        return $this->instantiateEditor([], '__INDEX__');
    }

    /**
     * Instantiate a new RowEditor instance using the supplied data.  The fields
     * for the RowEditor will be build by your fieldsCallback.  All fields will
     * then have their HTML ID and control names manipulated to allow for them
     * to be submitted back as arrays and to avoid naming collisions.
     *
     * @param array $data
     * @param mixed $index
     * @return RowEditor
     */
    private function instantiateEditor(array $data, $index = null)
    {
        $fields = new Fields();
        $editor = new RowEditor($fields, $this->request);

        call_user_func($this->fieldsCallback, $fields);

        foreach ($fields as $field) {
            $field
                ->setHtmlId($this->id . '_' . $field->getHtmlId() . '_' . ($index ?: count($this->editors)))
                ->setControlName($this->id . ':' . $field->getControlName() . '[]');
        }

        foreach ($fields->getModelsByName() as $name => $model) {
            $editor->setRowByData($name, $data);
        }

        if ($this->rowEditorCallback) {
            call_user_func($this->rowEditorCallback, $editor, $data);
        }

        $fields->add($this->createDeleteField($editor));

        return $editor;
    }

    /**
     * Get the data for this collection from the supplied request data.
     *
     * @param array $request
     * @param string $mode
     * @return array
     */
    private function getDataFromRequest(array $request, $mode)
    {
        $prefix = preg_quote($this->id . ':', '/');

        $dataByField = [];
        $dataByRow   = [];

        foreach ($request as $id => $value) {
            if (preg_match("/^{$prefix}/", $id)) {
                if (self::DATA_MODE_CONTROL_NAMES === $mode) {
                    $dataByField[$id . '[]'] = $value;
                } else {
                    $id = substr($id, strrpos($id, ':') + 1);
                    $dataByField[$id] = $value;
                }
            }
        }

        foreach ($dataByField as $field => $values) {
            foreach (array_values($values) as $index => $value) {
                if (!array_key_exists($index, $dataByRow)) {
                    $dataByRow[$index] = [];
                }

                $dataByRow[$index][$field] = $value;
            }
        }

        return $dataByRow;
    }

    /**
     * Create a Field object for handling deletion of items.
     *
     * @param RowEditor $editor
     * @return Field
     */
    private function createDeleteField(RowEditor $editor)
    {
        $field = new Field();

        $field
            ->setId('delete')
            ->setEditable(true)
            ->assignHelperCallback(
                'InputFilter',
                function () {
                    $input = new \Zend\InputFilter\Input('delete');
                    $input->setAllowEmpty(true);
                    return $input;
                }
            )
            ->assignHelperCallback(
                'EditControl.Label',
                function () {
                    return '<span class="glyphicon glyphicon-trash"></span>';
                }
            )
            ->assignHelperCallback(
                'EditControl.Control',
                function () use ($editor) {
                    if ($editor->isNew()) {
                        $out = '<button data-is-new="1" class="btn btn-danger btn-delete">';
                    } elseif ($editor->hasDeleteField()) {
                        $out = '<button data-is-new="0" class="btn btn-danger btn-delete">';
                    } else {
                        $out = '<button data-is-new="0" class="btn btn-danger btn-delete disabled">';
                    }

                    $out .= '<span class="glyphicon glyphicon-trash"></span>';
                    $out .= '</button>';

                    return $out;
                }
            );

        return $field;
    }
}
