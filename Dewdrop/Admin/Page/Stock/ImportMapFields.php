<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;
use Dewdrop\Fields;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Filter\Groups as GroupsFilter;
use Dewdrop\Fields\Helper\EditControl;
use Dewdrop\Fields\RowEditor;
use Dewdrop\Import\DbGateway as ImportGateway;
use Dewdrop\Import\File as ImportFile;

class ImportMapFields extends StockPageAbstract
{
    /**
     * @var ComponentAbstract|CrudInterface
     */
    protected $component;

    /**
     * @var ImportFile
     */
    private $importFile;

    /**
     * @var ImportGateway
     */
    private $importGateway;

    /**
     * @var array
     */
    private $importErrors;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $pluralTitle;

    /**
     * @var Fields
     */
    private $fields;

    /**
     * @var RowEditor
     */
    private $rowEditor;

    /**
     * @var GroupsFilter
     */
    private $fieldGroupsFilter;

    /**
     * @var string
     */
    private $primaryRowName;

    public function init()
    {
        $this->getRowEditor()->link();

        $this->importGateway = $this->getImportGateway();
        $this->importFile    = $this->importGateway->loadFile($this->request->getQuery('id'));
    }

    public function process(ResponseHelper $helper)
    {
        if ($this->request->isPost()) {
            $importRows = $this->importFile->getData();
            $inputRows  = [];
            $fields     = $this->getFields()->getEditableFields();

            foreach ($importRows as $row) {
                $data = [];

                foreach ($fields as $field) {
                    $id     = $field->getId();
                    $modeId = $id . ':mode';
                    $mode   = $this->request->getPost($modeId);

                    $data[$id] = $this->getFieldValue($id, $mode, $row);
                }

                $inputRows[] = $data;
            }

            if ($this->isValid($fields, $importRows, $inputRows)) {
                $this->save($inputRows);

                $count = count($importRows);

                $helper
                    ->setSuccessMessage("Successfully imported {$count} {$this->getPluralTitle()}.")
                    ->redirectToAdminPage('index');
            }
        }
    }

    public function render()
    {
        $renderer = $this->view->editControlRenderer();

        $this->getView()->assign(
            [
                'title'             => $this->getTitle(),
                'pluralTitle'       => $this->getPluralTitle(),
                'importFile'        => $this->importFile,
                'importErrors'      => $this->importErrors,
                'fields'            => $this->decorateFields($this->getFields(), $renderer),
                'rowEditor'         => $this->getRowEditor(),
                'renderer'          => $renderer,
                'fieldGroupsFilter' => $this->getFieldGroupsFilter()
            ]
        );
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        if (!$this->title) {
            $this->title = $this->component->getTitle();
        }

        return $this->title;
    }

    public function setPluralTitle($pluralTitle)
    {
        $this->pluralTitle = $pluralTitle;

        return $this;
    }

    public function getPluralTitle()
    {
        if (!$this->pluralTitle) {
            $this->pluralTitle = $this->component->getPrimaryModel()->getPluralTitle();
        }

        return $this->pluralTitle;
    }

    public function setFields(Fields $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    public function getFields()
    {
        if (!$this->fields) {
            $this->fields = $this->component->getFields();
        }

        return $this->fields;
    }

    public function setRowEditor(RowEditor $rowEditor)
    {
        $this->rowEditor = $rowEditor;

        return $this;
    }

    public function getRowEditor()
    {
        if (!$this->rowEditor) {
            $this->rowEditor = $this->component->getRowEditor();
        }

        return $this->rowEditor;
    }

    public function setFieldGroupsFilter(GroupsFilter $fieldGroupsFilter)
    {
        $this->fieldGroupsFilter = $fieldGroupsFilter;

        return $this;
    }

    public function getFieldGroupsFilter()
    {
        if (!$this->fieldGroupsFilter) {
            $this->fieldGroupsFilter = $this->component->getFieldGroupsFilter();
        }

        return $this->fieldGroupsFilter;
    }

    public function setPrimaryRowName($primaryRowName)
    {
        $this->primaryRowName = $primaryRowName;

        return $this;
    }

    public function getPrimaryRowName()
    {
        if (!$this->primaryRowName) {
            $this->primaryRowName = $this->component->getPrimaryModel()->getTableName();
        }

        return $this->primaryRowName;
    }

    public function setImportGateway(ImportGateway $importGateway)
    {
        $this->importGateway = $importGateway;

        return $this;
    }

    public function getImportGateway()
    {
        if (!$this->importGateway) {
            $this->importGateway = new ImportGateway();
        }

        return $this->importGateway;
    }

    /**
     * Not 100% certain that resetting and re-linking will be sufficient in all
     * cases for the row editor initialization so providing this little hook to
     * allow custom initialization logic by a sub-class.
     *
     * @param RowEditor $rowEditor
     * @return $this
     */
    protected function initializeRowEditor(RowEditor $rowEditor)
    {
        $rowEditor
            ->reset()
            ->link();

        return $this;
    }

    private function decorateFields(Fields $fields, EditControl $renderer)
    {
        $control = $renderer->getControlRenderer();

        /* @var $field FieldInterface */
        foreach ($fields->getEditableFields() as $field) {
            $callback = $control->getFieldAssignment($field);

            $control->assign(
                $field,
                function () use ($callback, $control, $field) {
                    return $control->getView()->importEditControl(
                        $field,
                        $this->importFile,
                        $this->request,
                        $callback($control, $control->getView())
                    );
                }
            );
        }

        return $fields;
    }

    private function getFieldValue($id, $mode, array $importRow)
    {
        switch ($mode) {
            case 'value':
                return $this->request->getPost($id);
            case 'column':
               $column = $this->request->getPost($id . ':column');

                if (isset($importRow[$column])) {
                    return $importRow[$column];
                } else {
                    return null;
                }
            case 'blank':
            default:
                return null;
        }
    }

    private function isValid(Fields $fields, array $importRows, array $inputRows)
    {
        $rowEditor = $this->getRowEditor();
        $isValid   = true;

        foreach ($inputRows as $index => $inputData) {
            $this->initializeRowEditor($rowEditor);

            if (!$rowEditor->isValid($inputData)) {
                $isValid = false;
                $errors  = [];

                foreach ($fields as $field) {
                    if ('column' === $this->request->getPost($field->getId() . ':mode')) {
                        $column   = $this->request->getPost($field->getId() . ':column');
                        $messages = $rowEditor->getMessages($field);

                        if ($messages) {
                            $errors[$column] = $messages;
                        }
                    }
                }

                if (count($errors)) {
                    $this->importErrors[] = [
                        'rowNumber' => $index + 1,
                        'data'      => $importRows[$index],
                        'errors'    => $errors
                    ];
                }
            }
        }

        return $isValid;
    }

    protected function save(array $inputRows)
    {
        $rowEditor = $this->getRowEditor();

        foreach ($inputRows as $inputData) {
            $this->initializeRowEditor($rowEditor);

            /**
             * Not ideal, but we call isValid() a second time here to ensure the row editor
             * is populated with the data from each import row.  isValid() does this population
             * in part because the input filter is needed to filter values before population.
             */
            $rowEditor->isValid($inputData);

            $rowEditor->save();

            $primaryRow   = $rowEditor->getRow($this->getPrimaryRowName());
            $primaryName  = current($primaryRow->getTable()->getPrimaryKey());
            $primaryValue = $primaryRow->get($primaryName);

            $this->importGateway->insertFile(
                [
                    'dewdrop_import_file_id'   => $this->request->getQuery('id'),
                    'record_primary_key_value' => $primaryValue
                ]
            );
        }

        return $this;
    }
}
