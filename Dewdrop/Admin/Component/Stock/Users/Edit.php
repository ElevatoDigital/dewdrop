<?php

namespace Dewdrop\Admin\Component\Stock\Users;

use Dewdrop\Admin\Page\Stock\Edit as StockEdit;
use Dewdrop\Pimple;

class Edit extends StockEdit
{
    protected function checkPermissions()
    {
        $permissions = $this->component->getPermissions();
        $user        = Pimple::getResource('user');

        if ($user->get('user_id') !== (int) $this->request->getQuery('user_id')) {
            parent::checkPermissions();
        }
    }

    public function render()
    {
        $this->view->setScriptPath(__DIR__ . '/../../../Page/Stock/view-scripts');
        parent::render();
    }
}
