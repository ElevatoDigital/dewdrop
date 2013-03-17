<?php

namespace Dewdrop\Admin\Page;

use Dewdrop\Admin\ComponentAbstract;
use Dewdrop\Fields\Edit as EditFields;

abstract class EditAbstract extends PageAbstract
{
    public function __construct(ComponentAbstract $component, $pageFile)
    {
        parent::__construct($component, $pageFile, false);

        $this->fields = new EditFields();
    }

    public function shouldProcess()
    {
        return $this->request->isPost();
    }

    public function findRowById($modelClass)
    {
        if (false === strpos($modelClass, '\\')) {
            $modelClass = '\Model\\' . $modelClass;
        }

        $model = new $modelClass($this->component->getDb());
        $pkey  = $model->getPrimaryKey();
        $query = $this->request->getQuery();
        $id    = array();

        foreach ($pkey as $column) {
            if (isset($query[$column]) && $query[$column]) {
                $id[] = $query[$column];
            }
        }

        if (!count($id)) {
            $this->view->title = "Add New {$model->getSingularTitle()}";

            return $model->createRow();
        } else {
            $this->view->title = "Edit {$model->getSingularTitle()}";

            return call_user_func_array(
                array($model, 'find'),
                $id
            );
        }
    }
}
