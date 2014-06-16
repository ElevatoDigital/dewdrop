<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Fields;
use Dewdrop\Fields\GroupedFields;
use Dewdrop\Fields\Helper\TableCell as Renderer;

class BootstrapDetailsView extends AbstractHelper
{
    public function direct(Fields $fields, array $data, Renderer $renderer = null)
    {
        if (null === $renderer) {
            $renderer = $this->view->tableCellRenderer();
        }

        if ($fields instanceof GroupedFields) {
            return $this->renderGroups($fields, $data, $renderer);
        } else {
            return $this->renderFields($fields, $data, $renderer);
        }
    }

    protected function renderGroups(GroupedFields $fields, array $data, Renderer $renderer)
    {
        $output = '<ul class="nav nav-tabs">';

        foreach ($fields->getGroups() as $index => $group) {
            if (count($group)) {
                $output .= sprintf(
                    '<li%s><a href="#group_%d" data-toggle="tab">%s</a></li>',
                    (0 === $index ? ' class="active"' : ''),
                    $index,
                    $this->view->escapeHtml($group->getTitle())
                );
            }
        }

        $output .= '</ul>';
        $output .= '<div class="tab-content">';

        foreach ($fields->getGroups() as $index => $group) {
            if (count($group)) {
                $output .= sprintf(
                    '<div class="tab-pane-edit tab-pane fade%s" id="group_%d">',
                    (0 === $index ? ' in active' : ''),
                    $index
                );

                $output .= $this->renderFields($group, $data, $renderer);

                $output .= '</div>';
            }
        }

        $output .= '</div>';

        return $output;
    }

    protected function renderFields(Fields $fields, array $data, Renderer $renderer)
    {
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
