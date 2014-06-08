<?php

namespace Dewdrop\Admin\Page\Stock\Silex;

use Dewdrop\Admin\Page\PageAbstract;
use SqlFormatter;

class DebugListingSql extends PageAbstract
{
    public function render()
    {
        $select = $this->component->getListing()->getModifiedSelect($this->component->getFields());

        $this->view->formattedSql = SqlFormatter::format((string) $select);
    }
}
