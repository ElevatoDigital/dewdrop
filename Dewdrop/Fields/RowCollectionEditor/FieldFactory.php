<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\RowCollectionEditor;

use Dewdrop\Fields\Field;
use Dewdrop\Fields\Helper\EditControl\Control as EditControl;
use Dewdrop\Fields\Helper\TableCell\Content as TableCell;
use Dewdrop\Fields\RowCollectionEditor;

/**
 * This class can create a custom field object for your RowCollectionEditor,
 * allowing you to integrate it with a Fields object for editing, validation
 * and viewing.
 *
 * By default, this class will construct a field with editing enabled and a
 * suitable input filter implementation for validation.  The visible permission
 * is disabled by default because you'll need to ensure that the data you're
 * rendering with contains the information the renderer needs.  When rendering
 * in a table view, it will look for the RowCollectionEditor's ID in your
 * row data and render that as a count (e.g. "2 Contacts").  In a detail view,
 * it will render a table displaying all the data from the RowCollectionEditor.
 */
class FieldFactory
{
    /**
     * The view helper to use when editing the RowCollectionEditor.
     *
     * @var string
     */
    private $editViewHelperName = 'rowCollectionInputTable';

    /**
     * Any options to pass along to the view helper when editing.
     *
     * @var array
     */
    private $editViewHelperOptions = [];

    /**
     * The name of the view helper you'd like to use to render table cell content.
     *
     * @var string
     */
    private $tableCellViewHelperName = 'rowCollectionCellContent';

    /**
     * Any options you'd like to supply to the table cell view helper.
     *
     * @var array
     */
    private $tableCellViewHelperOptions = [];

    /**
     * The field we'll look for in your row data when rendering in a table view.
     * By default, we use the ID of the RowCollectionEditor for this.
     *
     * @var string
     */
    private $tableCellMapping;

    /**
     * The RowCollectionEditor that we're generated the Field object for.
     *
     * @var RowCollectionEditor
     */
    private $rowCollectionEditor;

    /**
     * A factory for generating the input filter.
     *
     * @var InputFilterFactory
     */
    private $inputFilterFactory;

    /**
     * Provide the RowCollectionEditor for which we'll be generating the field
     * object.
     *
     * @param RowCollectionEditor $rowCollectionEditor
     */
    public function __construct(RowCollectionEditor $rowCollectionEditor)
    {
        $this->rowCollectionEditor = $rowCollectionEditor;
        $this->tableCellMapping    = $this->rowCollectionEditor->getId();
    }

    /**
     * Create the new field object.
     *
     * @return Field
     */
    public function createInstance()
    {
        $field = new Field();

        $field
            ->setId($this->rowCollectionEditor->getId())
            ->setLabel($this->rowCollectionEditor->getTitle())
            ->setEditable(true)
            ->setVisible(false)
            ->assignHelperCallback(
                'SaveHandler',
                function () {
                    return $this->rowCollectionEditor;
                }
            )
            ->assignHelperCallback(
                'EditControl.Control',
                function (EditControl $helper) {
                    $helperName = $this->editViewHelperName;
                    return $helper->getView()->$helperName($this->rowCollectionEditor, $this->editViewHelperOptions);
                }
            )
            ->assignHelperCallback(
                'EditControl.Label',
                function () {
                    return null;
                }
            )
            ->assignHelperCallback(
                'InputFilter',
                function () {
                    return $this->getInputFilterFactory()->createInstance();
                }
            )
            ->assignHelperCallback(
                'TableCell.Content',
                function (TableCell $helper, array $rowData) {
                    $helperName = $this->tableCellViewHelperName;

                    return $helper->getView()->$helperName(
                        $this->rowCollectionEditor,
                        array_merge(
                            [
                                'rowData'  => $rowData,
                                'mapping'  => $this->tableCellMapping,
                                'renderer' => $helper
                            ],
                            $this->tableCellViewHelperOptions
                        )
                    );
                }
            );

        return $field;
    }

    /**
     * Set the name of the index in row data that will be used when rendering
     * table cell content.  Defaults to the ID of the RowCollectionEditor.
     *
     * @param $tableCellMapping
     * @return $this
     */
    public function setTableCellMapping($tableCellMapping)
    {
        $this->tableCellMapping = $tableCellMapping;

        return $this;
    }

    /**
     * Set an alternate factory for the input filter, if you need to use
     * different validation/filtering logic.
     *
     * @param InputFilterFactory $inputFilterFactory
     * @return $this
     */
    public function setInputFilterFactory(InputFilterFactory $inputFilterFactory)
    {
        $this->inputFilterFactory = $inputFilterFactory;

        return $this;
    }

    /**
     * Get the factory for generating the \Zend\InputFilter\Input.
     *
     * @return InputFilterFactory
     */
    public function getInputFilterFactory()
    {
        if (!$this->inputFilterFactory) {
            $this->inputFilterFactory = new InputFilterFactory($this->rowCollectionEditor);
        }

        return $this->inputFilterFactory;
    }

    /**
     * Set the name of the view helper used for editing the RowCollectionEditor's
     * data.
     *
     * @param string $editViewHelperName
     * @return $this
     */
    public function setEditViewHelperName($editViewHelperName)
    {
        $this->editViewHelperName = $editViewHelperName;

        return $this;
    }

    /**
     * Set an array of options to pass along to the view helper used to edit the
     * RowCollectionEditor's data.
     *
     * @param array $editViewHelperOptions
     * @return $this
     */
    public function setEditViewHelperOptions(array $editViewHelperOptions)
    {
        $this->editViewHelperOptions = $editViewHelperOptions;

        return $this;
    }

    /**
     * Set the name of the view helper that will be used when rendering a table
     * cell's content.
     *
     * @param string $tableCellViewHelperName
     * @return $this
     */
    public function setTableCellViewHelperName($tableCellViewHelperName)
    {
        $this->tableCellViewHelperName = $tableCellViewHelperName;

        return $this;
    }

    /**
     * Set an array of options to pass along to the view helper when rendering
     * table cell content.
     *
     * @param array $tableCellViewHelperOptions
     * @return $this
     */
    public function setTableCellViewHelperOptions(array $tableCellViewHelperOptions)
    {
        $this->tableCellViewHelperOptions = $tableCellViewHelperOptions;

        return $this;
    }
}
