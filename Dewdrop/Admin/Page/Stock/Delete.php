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
use Dewdrop\Fields\RowEditor;

/**
 * This page handles requests to delete items using a CRUD component's row
 * editor object.  It requires a POST request, to avoid simple attacks where
 * a user is tricked into navigating to a link that deletes a record.
 */
class Delete extends StockPageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * The result to return in the JSON response.
     *
     * @var string
     */
    private $result = 'error';

    /**
     * Ensure the user has the permission to delete records in this component.
     */
    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('delete');
    }

    /**
     * When receiving a POST, get the row editor setup and then call its
     * delete() method.
     */
    public function process()
    {
        if ($this->request->isPost()) {
            $rowEditor = $this->component->getRowEditor();
            $rowEditor->link();
            $rowEditor->delete();

            $this->logActivity($rowEditor);

            $this->result = 'success';
        }
    }

    protected function logActivity(RowEditor $rowEditor)
    {
        $model = $this->component->getPrimaryModel();
        $rows  = $rowEditor->getRows();
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
            $handler->delete($id);
        }
    }

    /**
     * If we've made it to render(), that means process() didn't work, so
     * communicate that back to the calling code.
     */
    public function render()
    {
        return ['result' => $this->result];
    }
}
