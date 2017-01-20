<?php

namespace Admin\Users;

use Dewdrop\Admin\Component\Stock\Users\Component as UsersComponent;
use Dewdrop\Fields;

class Component extends UsersComponent
{
    public function init()
    {
        parent::init();

        $this
            ->setTitle('Users');
    }

    /**
     * @return Fields\RowEditor
     */
    public function getRowEditor()
    {
        if (!$this->rowEditor) {
            parent::getRowEditor();
            $this->rowEditor->setDeleteField($this->getPrimaryModel()->field('deleted'));
        }

        return $this->rowEditor;
    }
}
