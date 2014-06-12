<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;
use SqlFormatter;

class DebugListingSql extends PageAbstract
{
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('debug');

        $select = $this->component->getListing()->getModifiedSelect($this->component->getFields());

        $this->view->formattedSql = SqlFormatter::format((string) $select);
    }
}
