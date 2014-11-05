<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Listing\BulkActions;

use Dewdrop\Fields\Listing\BulkActions;
use Dewdrop\View\View;

/**
 * A simple bulk action implementer that you can use when you just need
 * a single button that triggers a callback when selected.  Example usage:
 *
 * <pre>
 * $bulkActions = new BulkActions($listing, $fields);
 *
 * $bulkActions->addButton(
 *      'my_id',
 *      'Button Title',
 *      function (array $selectedItems) {
 *          // Your bulk action code here
 *      }
 * );
 */
class Button implements ActionInterface
{
    /**
     * The BulkActions object this button is assigned to.
     *
     * @var BulkActions
     */
    private $bulkActions;

    /**
     * The ID that should be used on the submit button used for this action.
     *
     * @var string
     */
    private $id;

    /**
     * The title to display on the button element.
     *
     * @var string
     */
    private $buttonTitle;

    /**
     * The callback that should be run when your action is selected.  The IDs
     * (defined by the primary key field on the Listing your BulkActions apply
     * to) of the selected items will be passed to your callable as an array.
     * Your callable will not be run if no items were selected.
     *
     * @var callable
     */
    private $callback;

    /**
     * Provide the BulkActions object this action is associated with.
     *
     * @param BulkActions $bulkActions
     */
    public function __construct(BulkActions $bulkActions)
    {
        $this->bulkActions = $bulkActions;
    }

    /**
     * Set the ID that should be used for the submit input of this action.
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the title of the button.
     *
     * @param string $buttonTitle
     * @return $this
     */
    public function setButtonTitle($buttonTitle)
    {
        $this->buttonTitle = $buttonTitle;

        return $this;
    }

    /**
     * Provide a callback to run when this action is selected.
     *
     * @param callable $callback
     * @return $this
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Button actions are processed when the submit input is available in POST
     * and has the title as its value.
     *
     * @return bool
     */
    public function shouldProcess()
    {
        return $this->bulkActions->getRequest()->isPost() &&
            $this->buttonTitle === $this->bulkActions->getRequest()->getPost($this->id);
    }

    /**
     * Run the action's callback.  Will only be called when there are items
     * selected, so you don't have to be paranoid about receiving an empty
     * array.
     *
     * @param array $selected
     * @return mixed
     */
    public function process(array $selected)
    {
        return call_user_func($this->callback, $selected);
    }

    /**
     * Render the submit button for this action.
     *
     * @param View $view
     * @return string
     */
    public function render(View $view)
    {
        return sprintf(
            '<input name="%s" type="submit" class="btn btn-primary" value="%s" />',
            $view->escapeHtmlAttr($this->id),
            $view->escapeHtmlAttr($this->buttonTitle)
        );
    }
}
