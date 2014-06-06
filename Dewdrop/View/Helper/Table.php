<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Exception;
use Dewdrop\Fields\Listing;
use Dewdrop\Fields\Helper\TableCell as TableCellHelper;

/**
 */
class Table extends AbstractHelper
{
    public function direct()
    {
        $args = func_get_args();

        // No argument passed, assume they want to call other methods
        if (0 === count($args)) {
            return $this;
        }

        if (!isset($args[0]) || !$args[0] instanceof Listing ||
            !isset($args[1]) || !$args[1] instanceof TableCellHelper
        ) {
            throw new Exception('Table helper takes either no arguments or a Listing object and TableCell helper.');
        } else {
            $listing    = $args[0];
            $cellHelper = $args[1];
            $filters    = (isset($args[2]) ? $args[2] : null);
            $fields     = $listing->getVisibleFields($filters);

            return $this->open()
                . $this->renderHead($fields, $cellHelper)
                . $this->renderBody($listing->fetchData(), $fields, $cellHelper)
                . $this->close();
        }
    }

    public function open()
    {
        return '<table cellspacing="0">';
    }

    public function close()
    {
        return '</table>';
    }

    public function renderHead(array $fields, TableCellHelper $renderer)
    {
        $out = '<thead>';

        foreach ($fields as $index => $field) {
            $out .= '<th scope="col">';

            $content = $renderer->getHeaderRenderer()->render($field);

            if (!$field->isSortable()) {
                $out .= $content;
            } else {
                $out .= $this->renderSortLink(
                    $content,
                    $field->getId(),
                    ('asc' === $this->view->getRequest()->getQuery('dir') ? 'desc' : 'asc')
                );
            }

            $out .= '</th>';
        }

        $out .= '</thead>';

        return $out;
    }

    public function renderBody(array $data, array $fields, TableCellHelper $renderer)
    {
        $rowIndex = 0;

        $out = '<tbody>';

        foreach ($data as $row) {
            $out .= '<tr>';

            $columnIndex = 0;

            foreach ($fields as $field) {
                $out .= sprintf(
                    '<td class="%s">',
                    $renderer->getTdClassNamesRenderer()->render($field, $row, $rowIndex, $columnIndex)
                );

                $out .= $renderer->getContentRenderer()->render($field, $row, $rowIndex, $columnIndex);

                $out .= '</td>';

                $columnIndex += 1;
            }

            $out .= '</tr>';

            $rowIndex += 1;
        }

        $out .= '</tbody>';

        return $out;
    }

    protected function renderSortLink($content, $fieldId, $direction)
    {
        return sprintf(
            '<a href="?sort=%s&dir=%s">%s</a>',
            $this->view->escapeHtmlAttr($fieldId),
            $this->view->escapeHtmlAttr($direction),
            $this->view->escapeHtml($content)
        );
    }
}
