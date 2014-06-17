<?php

namespace Dewdrop\View\Helper;

class BootstrapRowActions extends AbstractHelper
{
    public function assignCallback(array $options)
    {
        $this
            ->checkRequired($options, array('renderer', 'field', 'title', 'urlFields'))
            ->ensureArray($options, array('urlFields'))
            ->ensurePresent($options, array('view', 'edit'));

        extract($options);

        $edit = urldecode($edit);
        $view = urldecode($view);

        $renderer->getContentRenderer()
            ->assignCallbackByColumnPosition(
                0,
                function ($helper, $rowData, $rowIndex, $columnIndex) use ($renderer, $edit, $view, $field, $title, $urlFields) {
                    $out = call_user_func(
                        $renderer->getContentRenderer()->getFieldAssignment($field),
                        $rowData,
                        $rowIndex,
                        $columnIndex
                    );

                    $params = array_map(
                        function ($fieldName) use ($rowData) {
                            return $rowData[$fieldName];
                        },
                        $urlFields
                    );

                    $out .= $this->open();

                    if ($edit) {
                        $out .= $this->renderEdit(vsprintf($edit, $params));
                    }

                    if ($view) {
                        $out .= $this->renderView(vsprintf($view, $params), $rowIndex);
                    }

                    $out .= $this->close();

                    if ($view) {
                        $out .= $this->renderModal($rowIndex, $title);
                    }

                    return $out;
                }
            );
    }

    public function open()
    {
        return '<div class="btn-group btn-group-justified row-actions pull-right">';
    }

    public function close()
    {
        return '</div>';
    }

    public function renderEdit($url)
    {
        return sprintf(
            '<a data-keyboard-role="edit" class="btn btn-xs btn-default" href="%s">Edit</a>',
            $this->view->escapeHtmlAttr($url)
        );
    }

    public function renderView($url, $index)
    {
        return sprintf(
            '<a data-toggle="modal" data-target="#view-modal-%s" data-loading-text="..." data-keyboard-role="view" '
            . 'class="btn btn-xs btn-default" href="%s">View</a>',
            $this->view->escapeHtmlAttr($index),
            $this->view->escapeHtmlAttr($url)
        );
    }

    public function renderModal($index, $modalTitle)
    {
        return $this->partial(
            'bootstrap-row-actions-view.phtml',
            array(
                'index'      => $index,
                'modalTitle' => $modalTitle
            )
        );
    }
}
