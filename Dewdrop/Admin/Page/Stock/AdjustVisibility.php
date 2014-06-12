<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;

class AdjustVisibility extends PageAbstract
{
    public function process($responseHelper)
    {
        $this->component->getPermissions()->haltIfNotAllowed('adjust-columns');

        $selections = $this->request->getPost('visible_columns');

        if (is_array($selections)) {
            $this->component->getVisibilityFilter()->save($this->component->getFields(), $selections);
        }

        $responseHelper->redirectToAdminPage('index');
    }
}
