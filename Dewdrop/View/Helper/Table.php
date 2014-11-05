<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

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
        } else {
            return call_user_func_array([$this, 'directWithArgs'], $args);
        }
    }

    public function directWithArgs(
        Fields $fields,
        array $data,
        TableCellHelper $renderer = null,
        SelectSort $sorter = null
    ) {
        $fields = $fields->getVisibleFields();

        if (null === $renderer) {
            $renderer = $this->view->tableCellRenderer();
        }

        return $this->open()
            . $this->renderHead($fields, $renderer, $sorter)
            . $this->renderBody($fields, $data, $renderer)
            . $this->close();
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
            $classes = $renderer->getRowClasses($row);

            if (!is_array($classes) || !count($classes)) {
                $out .= '<tr>';
            } else {
                $out .= sprintf(
                    '<tr class="%s">',
                    implode(
                        ' ',
                        array_map([$this->view, 'escapeHtmlAttr'], $classes)
                    )
                );
            }

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
