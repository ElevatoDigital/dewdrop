<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\Component\DataCopierInterface;
use Dewdrop\Admin\Page\PageAbstract;

class Copy extends PageAbstract
{
    /**
     * @var ComponentAbstract|CrudInterface|DataCopierInterface
     */
    protected $component;

    public function render()
    {
        $this->view->assign(
            [
                'title'      => $this->component->getTitle(),
                'model'      => $this->component->getPrimaryModel(),
                'dataCopier' => $this->component->getDataCopier()
            ]
        );
    }
}
