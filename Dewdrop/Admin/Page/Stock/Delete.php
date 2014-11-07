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
use Dewdrop\Admin\Page\PageAbstract;

/**
 * This page handles requests to delete items using a CRUD component's row
 * editor object.  It requires a POST request, to avoid simple attacks where
 * a user is tricked into navigating to a link that deletes a record.
 */
class Delete extends PageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

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

            header('Content-Type: application/json');
            echo json_encode(['result' => 'success']);
            exit;
        }
    }

    /**
     * If we've made it to render(), that means process() didn't work, so
     * communicate that back to the calling code.
     */
    public function render()
    {
        header('Content-Type: application/json');
        echo json_encode(['result' => 'error']);
        exit;
    }
}
