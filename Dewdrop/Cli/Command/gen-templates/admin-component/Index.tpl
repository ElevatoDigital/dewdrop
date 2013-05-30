<?php

namespace Admin\{{namespace}};

use Dewdrop\Admin\Page\PageAbstract;
use Model\{{model}};

class Index extends PageAbstract
{
    private $model;

    private $rows;

    public function init()
    {
        $this->model = new {{model}}($this->component->getDb());
        $this->rows  = $this->model->fetchAdminListing();
    }

    public function render()
    {
        $this->view->assign(
            array(
                'primaryKeyColumns' => $this->model->getPrimaryKey(),
                'rows'              => $this->rows,
            )
        );
    }
}
