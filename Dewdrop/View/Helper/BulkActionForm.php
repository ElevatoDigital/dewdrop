<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Listing\BulkActions;

/**
 * Use this view helper to surround whatever markup contains your bulk action
 * checkboxes (typically a table, but could also be a "tile" view or other
 * Listing rendering) with a form and the controls for selecting a bulk
 * action.
 */
class BulkActionForm extends AbstractHelper
{
    /**
     * Open the form an inject CSS and JS dependencies into the view.
     *
     * @return string
     */
    public function open()
    {
        $this->view->headLink()->appendStylesheet($this->view->bowerUrl('/dewdrop/www/css/bulk-action-form.css'));
        $this->view->headScript()->appendFile($this->view->bowerUrl('/dewdrop/www/js/bulk-action-form.js'));

        return '<form class="bulk-action-form" action="" method="POST">';
    }

    /**
     * Close the form.  Will include the rendered controls for your actual bulk
     * actions (from your actions' render() methods).  We take the total listing
     * item count and number of items currently visible (numbers you'll typically
     * grab from the SelectPaginate Listing helper) so that the user can choose
     * to select all the items in the listing, rather than just those on the
     * currently visible page.  The plural title is used in that "select all" UI.
     *
     * @param BulkActions $bulkActions
     * @param integer $totalCount
     * @param integer $visibleCount
     * @param string $pluralTitle
     * @return string
     */
    public function close(BulkActions $bulkActions, $totalCount, $visibleCount, $pluralTitle = 'Items')
    {
        return $this->partial(
            'bulk-action-form-close.phtml',
            [
                'bulkActions'  => $bulkActions,
                'visibleCount' => $visibleCount,
                'totalCount'   => $totalCount,
                'pluralTitle'  => $pluralTitle
            ]
        );
    }
}
