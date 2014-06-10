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
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\SelectSort;
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

        if (!isset($args[0]) || !$args[0] instanceof Fields ||
            !isset($args[1]) || !is_array($args[1])
        ) {
            throw new Exception('Table helper takes either no arguments or an array of fields and an array of data');
        } else {
            $fields   = $args[0]->getVisibleFields();
            $data     = $args[1];
            $renderer = (isset($args[2]) ? $args[2] : $this->view->tableCellRenderer());
            $sorter   = (isset($args[3]) && $args[3] instanceof SelectSort ? $args[3] : null);

            return $this->open()
                . $this->renderHead($fields, $renderer, $sorter)
                . $this->renderBody($fields, $data, $renderer)
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

    public function renderHead(Fields $fields, TableCellHelper $renderer, SelectSort $sorter = null)
    {
        $out = '<thead>';

        foreach ($fields as $index => $field) {
            $out .= '<th scope="col">';

            $content = $renderer->getHeaderRenderer()->render($field);

            if (!$field->isSortable()) {
                $out .= $content;
            } else {
                $direction = 'asc';

                if ($sorter && $sorter->getSortedField() === $field && 'ASC' === $sorter->getSortedDirection()) {
                    $direction = 'desc';
                }

                $out .= $this->renderSortLink($content, $field->getQueryStringId(), $direction, $sorter);
            }

            $out .= '</th>';
        }

        $out .= '</thead>';

        return $out;
    }

    public function renderBody(Fields $fields, array $data, TableCellHelper $renderer)
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

    protected function renderSortLink($content, $fieldId, $direction, SelectSort $sorter = null)
    {
        return sprintf(
            '<a href="?sort=%s&dir=%s">%s</a>',
            $this->view->escapeHtmlAttr($fieldId),
            $this->view->escapeHtmlAttr($direction),
            $this->view->escapeHtml($content)
        );
    }
}
