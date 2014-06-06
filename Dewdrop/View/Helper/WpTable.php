<?php

namespace Dewdrop\View\Helper;

class WpTable extends Table
{
    public function open()
    {
        return '<table class="wp-list-table widefat fixed posts" cellspacing="0">';
    }

    public function renderHead(array $columns)
    {
        $out = '<thead>';

        foreach ($columns as $index => $column) {
            $out .= sprintf(
                '<th scope="col" id="col-%d" class="manage-column column-col-%d"%s><span>',
                $this->view->escapeHtmlAttr($index),
                $this->view->escapeHtmlAttr($index),
                ($column->isVisible() ? '' : ' style="display: none"')
            );

            $out .= $this->view->escapeHtml($column->renderHeader());
            $out .= '</span></th>';
        }

        $out .= '</thead>';

        return $out;
    }

    public function renderBody(array $data, array $columns)
    {
        $rowIndex = 0;

        $out = '<tbody id="the-list">';

        foreach ($data as $row) {
            $out .= sprintf(
                '<tr class="%s">',
                ($rowIndex % 2 ? 'odd' : 'even')
            );

            foreach ($columns as $index => $column) {
                $classes  = array();
                $contents = $column->renderCell($row, $classes);

                if (0 !== $index) {
                    $out .= sprintf(
                        '<td class="%s%scolumn-col-%d"%s>',
                        implode(' ', array_map(array($this->view, 'escapeHtmlAttr'), $classes)),
                        (0 === count($classes) ? '' : ' '),
                        $this->view->escapeHtmlAttr($index),
                        ($column->isVisible() ? '' : ' style="display: none"')
                    );
                    $out .= $contents;
                    $out .= '</td>';
                } else {
                    $out .= sprintf(
                        '<td class="%s%scolumn-col-%d"%s>',
                        implode(' ', array_map(array($this->view, 'escapeHtmlAttr'), $classes)),
                        (0 === count($classes) ? '' : ' '),
                        $this->view->escapeHtmlAttr($index),
                        ($column->isVisible() ? '' : ' style="display: none"')
                    );

                    $pkey = array_shift(array_keys($row));
                    $value = $row[$pkey];
                    $params = array($pkey => $value);

                    $out .= '<a href="' . $this->view->adminUrl('View', $params) . '"><strong>';
                    $out .= $contents;
                    $out .= '</strong></a>';
                    $out .= '<div class="row-actions">';
                    $out .= '<a href="' . $this->view->adminUrl('View', $params) . '">View</a>';
                    $out .= ' | ';
                    $out .= '<span class="trash"><a onclick="if (!confirm(\'Are you sure you want to delete this item?\')) return false;" href="' . $this->view->adminUrl('Delete', $params) . '">Delete</a></span>';
                    $out .= '</div>';
                    $out .= '</td>';
                }

            }

            $out .= '</tr>';

            $rowIndex += 1;
        }

        $out .= '</tbody>';

        return $out;
    }
}
