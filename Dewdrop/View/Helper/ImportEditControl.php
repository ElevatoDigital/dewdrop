<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\FieldInterface;
use Dewdrop\Import\File;
use Dewdrop\Request;

class ImportEditControl extends AbstractHelper
{
    public function direct(FieldInterface $field, File $importFile, Request $request, $originalHtml)
    {
        $this->view->headLink()->appendStylesheet($this->view->bowerUrl('/dewdrop/www/css/import-edit-control.css'));

        return $this->partial(
            'import-edit-control.phtml',
            [
                'field'        => $field,
                'importFile'   => $importFile,
                'request'      => $request,
                'originalHtml' => $originalHtml
            ]
        );
    }
}
