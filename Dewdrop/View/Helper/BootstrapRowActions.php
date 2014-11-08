<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

/**
 * This helper renders the little view/edit buttons on stock Dewdrop listings.
 * It's kind of clunky and definitely stretches the stateless view helper model
 * we've developed all view helpers upon so far to its limits, due to the sheer
 * number of params needed to render these ostensibly simple controls.
 *
 * Because it's kind of a stretch the assignCallback() method has rather
 * extensive documentation of the key-value options we expect.
 */
class BootstrapRowActions extends AbstractHelper
{
    /**
     * Assign a TableCell.Content callback to the supplied field, wrapping its
     * existing output with set of a buttons allowing a user to view or edit the
     * row's data.  The view option renders a Bootstrap modal, while the edit
     * button is simply a link.
     *
     * Here are the options that are required in the options array:
     *
     * <ol>
     *     <li>renderer: A TableCell field helper that will be used for rendering.</li>
     *     <li>field: The field that will have its output wrapped.</li>
     *     <li>title: A title for the view modal.</li>
     *     <li>urlFields: Params for URL query strings (e.g. the ID of the record).</li>
     * </ol>
     *
     * Here are the optional parameters:
     *
     * <ol>
     *     <li>view: The URL for the view button.</li>
     *     <li>edit: The URL for the edit button.</li>
     * </ol>
     *
     * If either is missing, we'll skip the button that would have used that URL.
     *
     * If you need an example of using this helper/method, look in the stock Index
     * page of the admin.
     *
     * @param array $options
     * @throws \Dewdrop\Exception
     */
    public function assignCallback(array $options)
    {
        $this
            ->checkRequired($options, array('renderer', 'field', 'title', 'urlFields'))
            ->ensureArray($options, array('urlFields'))
            ->ensurePresent($options, array('view', 'edit'));

        extract($options);

        $originalCallback = $renderer->getContentRenderer()->getFieldAssignment($field);

        $renderer->getContentRenderer()->assign(
            $field->getId(),
            function ($helper, $rowData, $rowIndex, $columnIndex) use ($originalCallback, $options) {
                extract($options);

                $edit = urldecode($edit);
                $view = urldecode($view);
                $out  = call_user_func($originalCallback, $rowData, $rowIndex, $columnIndex);

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

    /**
     * Open a wrapper div to surround the buttons.
     *
     * @return string
     */
    public function open()
    {
        return '<div class="btn-group btn-group-justified row-actions pull-right">';
    }

    /**
     * Close the wrapper div.
     *
     * @return string
     */
    public function close()
    {
        return '</div>';
    }

    /**
     * Draw the edit button.
     *
     * @param string $url
     * @return string
     */
    public function renderEdit($url)
    {
        return sprintf(
            '<a data-keyboard-role="edit" class="btn btn-xs btn-default" href="%s">Edit</a>',
            $this->view->escapeHtmlAttr($url)
        );
    }

    /**
     * Draw the view button.
     *
     * @param string $url
     * @param integer $index
     * @return string
     */
    public function renderView($url, $index)
    {
        return sprintf(
            '<a data-toggle="modal" data-target="#view-modal-%s" data-loading-text="..." data-keyboard-role="view" '
            . 'class="btn btn-xs btn-default" href="%s">View</a>',
            $this->view->escapeHtmlAttr($index),
            $this->view->escapeHtmlAttr($url)
        );
    }

    /**
     * Render the placeholder for the view modal.
     *
     * @todo Look into doing this completely client-side instead littering these placeholders in server response.
     *
     * @param integer $index
     * @param string $modalTitle
     * @return string
     */
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
