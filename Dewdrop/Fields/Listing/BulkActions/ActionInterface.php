<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Listing\BulkActions;

use Dewdrop\View\View;

/**
 * This is the interface you should implement to add an action to a BulkActions
 * object for your listing.
 */
interface ActionInterface
{
    /**
     * Determine whether your action should be processed.  Typically, you should
     * be adding some kind of input (perhaps a submit button with a known ID or
     * a hidden input that you set via some custom JavaScript) to the request
     * that you can use to detect when the user has chosen to run your particular
     * bulk action.
     *
     * @return bool
     */
    public function shouldProcess();

    /**
     * Process the provided array of selected IDs.  The IDs will always be legit
     * values from the Listing's primary key field.  This means that the ID was
     * presented to the user and they were allowed to select it.  Your process
     * method will also only be called when there is at least 1 selected value,
     * so you don't have to worry about empty array if you're using an IN
     * operator.
     *
     * @param array $selected
     * @return mixed
     */
    public function process(array $selected);

    /**
     * Render some controls for your action.  Could be as simple as a single
     * submit button or something more complex/dynamic using a view script
     * partial, custom CSS and JS, etc.
     *
     * @param View $view
     * @return mixed
     */
    public function render(View $view);
}
