<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component\Stock\Users;

use Dewdrop\Admin\Page\Stock\Edit as StockEdit;
use Dewdrop\Pimple;

/**
 * User edit page
 */
class Edit extends StockEdit
{
    /**
     * Check permissions
     *
     * @return void
     */
    protected function checkPermissions()
    {
        $user = Pimple::getResource('user');

        if ($user->get('user_id') !== (int) $this->request->getQuery('user_id')) {
            parent::checkPermissions();
        }
    }

    /**
     * Handle submission of the edit form.  This primarily exists (rather
     * than using the stock Edit->process()) for the password hashing.
     *
     * @todo Find a better way to handle the password hashing.
     *
     * @param \Dewdrop\Admin\ResponseHelper\Standard $responseHelper
     * @return void
     */
    public function process($responseHelper)
    {
        if ($this->request->isPost()) {
            $this->invalidSubmission = (!$this->rowEditor->isValid($this->request->getPost()));

            if (!$this->invalidSubmission) {
                $title = strtolower($this->model->getSingularTitle());

                /* @var $row \Dewdrop\Auth\Db\UserRowGateway */
                $row = $this->rowEditor->getRow($this->model->getTableName());

                if ($this->isNew) {
                    $row->hashPassword($this->request->getPost('password'));
                    $responseHelper->setSuccessMessage("Successfully saved new {$title}");
                } else {
                    $responseHelper->setSuccessMessage("Successfully saved changes to {$title}");
                }

                $row->save();

                if (!$this->request->isAjax()) {
                    $responseHelper->redirectToAdminPage('index');
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'result' => 'success',
                        'id'     => $this->component->getListing()->getPrimaryKey()->getValue()
                    ]);
                    exit;
                }
            }
        }
    }

    /**
     * Assign variables to your page's view and render the output.
     *
     * @return void
     */
    public function render()
    {
        $this->view->setScriptPath(__DIR__ . '/../../../Page/Stock/view-scripts');
        parent::render();
    }
}
