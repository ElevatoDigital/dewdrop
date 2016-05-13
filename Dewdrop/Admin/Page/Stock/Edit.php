<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;
use Dewdrop\Session;

/**
 * This page uses a RowEditor and a couple view helpers (primarily bootstrapForm())
 * to provide input validation and saving capabilities to a CRUD component.
 */
class Edit extends StockPageAbstract
{
    /**
     * Display the save button at the top of the page.
     *
     * @const
     */
    const SAVE_BUTTON_TOP = 1;

    /**
     * Display the save button at the bottom of the page.
     *
     * @const
     */
    const SAVE_BUTTON_BOTTOM = 2;

    /**
     * Display save button in both top and bottom positions.
     *
     * @const
     */
    const SAVE_BUTTON_BOTH = 3;

    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * The row editor we'll use to actually perform the validation and editing.
     *
     * @var \Dewdrop\Fields\RowEditor
     */
    protected $rowEditor;

    /**
     * Was an invalid submission made on this request?
     *
     * @var bool
     */
    protected $invalidSubmission = false;

    /**
     * Is the record that we're editing new or an existing record being edited?
     *
     * @var bool
     */
    protected $isNew;

    /**
     * The primary model from the CRUD component.
     *
     * @var \Dewdrop\Db\Table
     */
    protected $model;

    /**
     * The fields used when rendering the edit form.
     *
     * @var \Dewdrop\Fields
     */
    protected $fields;

    /**
     * Where to display the save button.
     *
     * @var int
     */
    protected $saveButtonPosition = self::SAVE_BUTTON_BOTTOM;

    /**
     * Setup the row editor and check component permissions.
     */
    public function init()
    {
        $this->rowEditor = $this->getRowEditor();
        $this->model     = $this->getModel();
        $this->fields    = $this->getFields();

        // Ensure primary key field is instantiated so that it is linked by row editor
        $this->component->getFields()->add($this->component->getListing()->getPrimaryKey())
            ->setEditable(false);

        $this->rowEditor->link();

        $this->isNew = $this->rowEditor->isNew();

        $this->checkPermissions();
    }

    /**
     * Set the save button position.  You can use the SAVE_BUTTON_BOTTOM,
     * SAVE_BUTTON_TOP and SAVE_BUTTON_BOTH class constants with this
     * method.
     *
     * @param int $saveButtonPosition
     * @return $this
     */
    public function setSaveButtonPosition($saveButtonPosition)
    {
        $this->saveButtonPosition = $saveButtonPosition;

        return $this;
    }

    /**
     * @return \Dewdrop\Fields\RowEditor
     */
    public function getRowEditor()
    {
        return $this->component->getRowEditor();
    }

    protected function getModel()
    {
        return $this->component->getPrimaryModel();
    }

    protected function getFields()
    {
        return $this->component->getFields($this->component->getFieldGroupsFilter());
    }

    /**
     * Ensure the user has permission to create or edit records on this CRUD
     * component.
     */
    protected function checkPermissions()
    {
        if ($this->isNew) {
            $this->component->getPermissions()->haltIfNotAllowed('create');
        } else {
            $this->component->getPermissions()->haltIfNotAllowed('edit');
        }
    }

    /**
     * On a POST request, validate the user's input.  If valid, save using the
     * RowEditor, set a success message and then redirect.  Should also behave
     * reasonably well when used as an endpoint for an XHR by returning a success
     * message and the new primary key value.
     *
     * @param ResponseHelper $responseHelper
     */
    public function process(ResponseHelper $responseHelper)
    {
        if ($this->request->isPost()) {
            $this->invalidSubmission = (!$this->rowEditor->isValid($this->request->getPost()));

            if (!$this->invalidSubmission) {
                $title = strtolower($this->model->getSingularTitle());

                if (!$this->request->isAjax()) {
                    if ($this->isNew) {
                        $responseHelper->setSuccessMessage("Successfully saved new {$title}.");
                    } else {
                        $responseHelper->setSuccessMessage("Successfully saved changes to {$title}.");
                    }
                }

                $this->rowEditor->save();

                $this->logActivity();

                if (!$this->request->isAjax()) {
                    $this->redirect($responseHelper);
                }
            }
        }
    }

    protected function logActivity()
    {
        $model = $this->component->getPrimaryModel();
        $rows  = $this->rowEditor->getRows();
        $id    = null;

        /* @var $row \Dewdrop\Db\Row */
        foreach ($rows as $row) {
            if ($row->getTable() === $model) {
                $id = $row->get(current($model->getPrimaryKey()));
            }
        }

        if ($id) {
            /* @var $handler \Dewdrop\ActivityLog\Handler\CrudHandlerAbstract */
            $handler = $this->component->getActivityLogHandler();

            if ($this->isNew) {
                $handler->create($id);
            } else {
                $handler->edit($id);
            }
        }
    }

    protected function redirect(ResponseHelper $responseHelper)
    {
        $session = new Session();
        $index   = $this->component->getListingQueryParamsSessionName();
        $params  = (isset($session[$index]) ? $session[$index] : []);

        $responseHelper->redirectToAdminPage('index', $params);
    }

    public function assignDefaultViewArguments()
    {
        $this->view->assign(
            [
                'component'          => $this->component,
                'isNew'              => $this->isNew,
                'fields'             => $this->fields->getEditableFields($this->component->getFieldGroupsFilter()),
                'model'              => $this->model,
                'rowEditor'          => $this->rowEditor,
                'request'            => $this->request,
                'invalidSubmission'  => $this->invalidSubmission,
                'saveButtonPosition' => $this->saveButtonPosition
            ]
        );
    }

    /**
     * Pass a bunch of stuff to the view.  Duh.
     */
    public function render()
    {
        if ($this->request->isAjax()) {
            return $this->renderAjaxResponse();
        } else {
            $this->assignDefaultViewArguments();
            return $this->renderView();
        }
    }

    public function renderAjaxResponse()
    {
        if (!$this->request->isPost() && !$this->request->isGet()) {
            return ['result' => 'error', 'message' => 'AJAX edit requests must be POST or GET'];
        } elseif ($this->request->isPost() && !$this->invalidSubmission) {
            return [
                'result'    => 'success',
                'id'        => $this->component->getListing()->getPrimaryKey()->getValue(),
                'data'      => $this->getData()
            ];
        } elseif ($this->request->isGet()) {
            return $this->renderAjaxForm();
        } else {
            $messages = [];

            foreach ($this->fields->getEditableFields() as $field) {
                $messages[$field->getHtmlId()] = $this->rowEditor->getMessages($field);
            }

            return [
                'result'   => 'invalid',
                'messages' => $messages
            ];
        }
    }

    public function renderAjaxForm()
    {
        $this->assignDefaultViewArguments();
        $this->component->setShouldRenderLayout(false);

        return $this->view->render('edit-fields-for-ajax.phtml');
    }

    public function getData()
    {
        $fields = $this->getFields()->getEditableFields();
        $data   = [];

        foreach ($fields as $id => $field) {
            $data[$id] = $field->getValue();
        }

        return $data;
    }
}
