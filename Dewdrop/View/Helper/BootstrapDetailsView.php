<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Fields;
use Dewdrop\Fields\Helper\TableCell as Renderer;

class BootstrapDetailsView extends AbstractHelper
{
    public function direct(Fields $fields, array $data, Renderer $renderer = null)
    {
        if (null === $renderer) {
            $renderer = $this->view->tableCellRenderer();
        }

        $rowIndex = 0;

        $out = '<div class="table-responsive">';
        $out .= '<table class="table">';

        foreach ($fields->getVisibleFields() as $field) {
            $out .= '<tr>';

            $out .= '<th scope="row">';
            $out .= $renderer->getHeaderRenderer()->render($field);
            $out .= '</th>';

            $out .= sprintf(
                '<td class="%s">',
                $renderer->getTdClassNamesRenderer()->render($field, $data, $rowIndex, 1)
            );

            $out .= $renderer->getContentRenderer()->render($field, $data, $rowIndex, 1);

            $out .= '</td>';
            $out .= '</tr>';

            $rowIndex += 1;
        }

        $out .= '</table>';
        $out .= '</div>';

        return $out;
    }
}
