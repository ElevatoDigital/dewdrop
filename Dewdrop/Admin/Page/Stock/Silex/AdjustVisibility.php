<?php

namespace Dewdrop\Admin\Page\Stock\Silex;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;

class AdjustVisibility extends PageAbstract
{
    public function process($responseHelper)
    {
        $selections = $this->request->getPost('visible_columns');

        if (is_array($selections)) {
            $this->component->getVisibilityFilter()->save($selections);
        }

        $responseHelper->redirectToAdminPage('index');
    }
}
