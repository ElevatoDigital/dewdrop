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
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\GroupedFields;
use Dewdrop\Fields\Helper\TableCell as Renderer;

/**
 * Render a table that displays a detailed view for a single item/entity.  The
 * first column in the table will be headers (&lt;th&gt;) containing the field
 * labels.  The second column will be the content of those fields rendered using
 * the TableCell.Content field helper.
 *
 * Note that this view helper does support \Dewdrop\Fields\GroupedFields as well.
 * If given grouped fields, it will render the fields in a tab view.
 */
class BootstrapDetailsView extends AbstractHelper
{
    /**
     * Supply fields and data with which to render the details view.  You can
     * optionally supply a \Dewdrop\Fields\Helper\TableCell object as a renderer,
     * if you need to assign specific callbacks, etc.
     *
     * @param Fields $fields
     * @param array $data
     * @param Renderer $renderer
     * @param integer $viewIndex
     * @return string
     */
    public function direct(Fields $fields, array $data, Renderer $renderer = null, $viewIndex = null)
    {
        if (null === $renderer) {
            $renderer = $this->view->tableCellRenderer()->getContentRenderer()
                ->setViewMode(Renderer\Content::VIEW_MODE_DETAIL);
        }

        /**
         * Only render groups in a tab view if there is more than 1 group because
         * when there is only 1 group, that means only the default "ungrouped"
         * or "other" set is present.
         */
        if ($fields instanceof GroupedFields && 1 < count($fields->getGroups())) {
            return $this->renderGroups($fields, $data, $renderer, $viewIndex);
        } else {
            return $this->renderFields($fields, $data, $renderer);
        }
    }

    /**
     * Render the supplied GroupedFields object in a Bootstrap tab view.
     *
     * @param GroupedFields $fields
     * @param array $data
     * @param Renderer $renderer
     * @param integer $viewIndex
     * @return string
     */
    protected function renderGroups(GroupedFields $fields, array $data, Renderer $renderer, $viewIndex = null)
    {
        static $globalGroupedViewCount = 0;

        $output = '<ul class="nav nav-tabs">';

        foreach ($fields->getGroups() as $index => $group) {
            if (count($group)) {
                $output .= sprintf(
                    '<li%s><a href="#details_%d_group_%d" data-toggle="tab">%s</a></li>',
                    (0 === $index ? ' class="active"' : ''),
                    $this->view->escapeHtmlAttr(null !== $viewIndex ? $viewIndex : $globalGroupedViewCount),
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
                    '<div class="tab-pane-edit tab-pane fade%s" id="details_%d_group_%d">',
                    (0 === $index ? ' in active' : ''),
                    $this->view->escapeHtmlAttr(null !== $viewIndex ? $viewIndex : $globalGroupedViewCount),
                    $index
                );

                $output .= $this->renderFields($group, $data, $renderer);

                $output .= '</div>';
            }
        }

        $output .= '</div>';

        $globalGroupedViewCount += 1;

        return $output;
    }

    /**
     * Render the supplied non-grouped fields in a Bootstrap table.
     *
     * @param Fields $fields
     * @param array $data
     * @param Renderer $renderer
     * @return string
     */
    protected function renderFields(Fields $fields, array $data, Renderer $renderer)
    {
        $rowIndex = 0;

        $out = '<div class="table-responsive">';
        $out .= '<table class="table">';

        foreach ($fields->getVisibleFields() as $field) {
            $out .= '<tr>';

            $content    = $renderer->getContentRenderer()->render($field, $data, $rowIndex, 1);
            $classNames = $renderer->getTdClassNamesRenderer()->render($field, $data, $rowIndex, 1);
            $header     = $renderer->getHeaderRenderer()->render($field);

            if (false !== stripos($content, '<table ')) {
                $out .= $this->renderTableContentInPanel($header, $classNames, $content);
            } else {
                $out .= $this->renderHeaderAndContent($header, $classNames, $content);
            }

            $out .= '</tr>';

            $rowIndex += 1;
        }

        $out .= '</table>';
        $out .= '</div>';

        return $out;
    }

    protected function renderTableContentInPanel($header, $classNames, $content)
    {
        return $this->partial(
            'bootstrap-details-view-table-panel.phtml',
            [
                'header'     => $header,
                'classNames' => $classNames,
                'content'    => $content
            ]
        );
    }

    protected function renderHeaderAndContent($header, $classNames, $content)
    {
        $out = '<th scope="row">';
        $out .= $header;
        $out .= '</th>';
        $out .= sprintf('<td class="%s">', $classNames);
        $out .= $content;
        $out .= '</td>';

        return $out;
    }
}
