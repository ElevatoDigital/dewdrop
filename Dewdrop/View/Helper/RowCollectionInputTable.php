<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\RowCollectionEditor;

/**
 * This view helper renders a table with a grid of inputs to allow editing with
 * a RowCollectionEditor object.  The user can dynamically add and remove rows
 * from the table.  If you have a large number of fields, this may not be a
 * workable UI for you because you'll be cramming too many columns into the table.
 *
 * The way this view helper works, all data handling is managed by the primary
 * form submission back to the server rather than via AJAX.  This ensures that
 * deletions and changes are not persisted unless the user actually submits
 * the form and avoids having to render any of the inputs in JavaScript, instead
 * re-using the EditControl rendering you've implemented already.
 */
class RowCollectionInputTable extends AbstractHelper
{
    /**
     * Provide the RowCollectionEditor needed to render the input table and queue
     * up the CSS and JS files needed on the client.
     *
     * @param RowCollectionEditor $rowCollectionEditor
     * @return string
     */
    public function direct(RowCollectionEditor $rowCollectionEditor)
    {
        $this->view->headScript()->appendFile(
            $this->view->bowerUrl('/dewdrop/www/js/row-collection-input-table.js')
        );

        $this->view->headLink()->appendStylesheet(
            $this->view->bowerUrl('/dewdrop/www/css/row-collection-input-table.css')
        );

        return $this->partial(
            'row-collection-input-table.phtml',
            [
                'rowCollectionEditor' => $rowCollectionEditor
            ]
        );
    }
}
