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
 * Render an HTML table using a Fields object and an array of data.  Uses
 * the TableCell field helper to allow you to customize the rendering of
 * each individual field.
 */
class Table extends AbstractHelper
{
    /**
     * If no arguments are provided, return this helper instance to allow
     * the user to call other methods individually.  Otherwise, use
     * directWithArgs() to validate the user's arguments and render the
     * table.
     *
     * @return $this|string
     */
    public function direct()
    {
        return $this->delegateIfArgsProvided(func_get_args());
    }

    /**
     * Render the full table using the supplied arguments.
     *
     * @param Fields $fields
     * @param array $data
     * @param null|TableCellHelper $renderer
     * @param null|SelectSort $sorter
     * @return string
     */
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

    /**
     * Render the opening table tag.
     *
     * @return string
     */
    public function open()
    {
        return '<table cellspacing="0">';
    }

    /**
     * Render the closing table tag.
     *
     * @return string
     */
    public function close()
    {
        return '</table>';
    }

    /**
     * Render the &lt;thead&gt; tag, using the TableCell.Header helper to allow
     * fields to modify their header content.
     *
     * @param Fields $fields
     * @param TableCellHelper $renderer
     * @param SelectSort $sorter
     * @return string
     */
    public function renderHead(Fields $fields, TableCellHelper $renderer, SelectSort $sorter = null)
    {
        $out = '<thead>';

        if (count($fields)) {
            $out .= '<tr>' . $this->renderHeadCells($fields, $renderer, $sorter) . '</tr>';
        }

        $out .= '</thead>';

        return $out;
    }

    /**
     * Render the &lt;tbody&gt; with the TableCell.Content helper to allow
     * each field's cell content to be modified.
     *
     * @param Fields $fields
     * @param array $data
     * @param TableCellHelper $renderer
     * @return string
     */
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

    /**
     * Render a sort link to display inside a &lt;th&gt; cell.
     *
     * @param string $content
     * @param string $fieldId
     * @param string $direction
     * @param SelectSort $sorter
     * @return string
     */
    protected function renderSortLink($content, $fieldId, $direction, SelectSort $sorter = null)
    {
        return sprintf(
            '<a href="?sort=%s&dir=%s">%s</a>',
            $this->view->escapeHtmlAttr($fieldId),
            $this->view->escapeHtmlAttr($direction),
            $this->view->escapeHtml($content)
        );
    }

    /**
     * Render all the &lt;th&gt; cells that will be contained in the &lt;thead&gt;.
     *
     * @param Fields $fields
     * @param TableCellHelper $renderer
     * @param SelectSort $sorter
     * @return string
     */
    protected function renderHeadCells(Fields $fields, TableCellHelper $renderer, SelectSort $sorter = null)
    {
        $out = '';

        foreach ($fields as $index => $field) {
            $out .= '<th scope="col">';

            $content = $renderer->getHeaderRenderer()->render($field);

            if (!$field->isSortable() || !$sorter) {
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

        return $out;
    }
}
