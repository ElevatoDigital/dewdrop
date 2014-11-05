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
     * Process the provided array of selected IDs.  The IDs will be integer
     * values.  In typical situations, the integers will represent primary key
     * values from your Listing, but it is user input, so don't assume that
     * each value actually exists in the DB.  In short, you have these
     * guarantees only:
     *
     * 1) Each value is a positive integer.
     *
     * 2) There will be at least one value (i.e. you can slide $selected into
     *    an IN (?) without worrying about it being empty).
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
