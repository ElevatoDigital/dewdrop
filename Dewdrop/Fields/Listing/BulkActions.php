<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Listing;

use Dewdrop\Fields;
use Dewdrop\Fields\Listing;
use Dewdrop\Fields\Listing\BulkActions\ActionInterface;
use Dewdrop\Fields\Listing\BulkActions\Button;
use Dewdrop\Fields\Listing\BulkActions\Result;

/**
 * The BulkActions class allows you to attach any number of actions to a
 * Listing.  The user (typically with some checkboxes on a table or tile
 * view) will select one or more items and then choose an action.
 *
 * The BulkActions class will then determine which action was selected
 * (using the shouldProcess() method on each action object) and pass the
 * IDs of the selected items to that action's process() method.  The values
 * in the array of IDs will be based upon whatever primary key field is
 * set in the Listing object.
 *
 * Note that there are two different ways BulkActions might select items:
 *
 * 1) An array of IDs explicitly checked by the user.
 *
 * 2) Retrieving a non-paginated, complete dataset from the Listing and
 *    using _all_ IDs.  This is done to allow users to select all items,
 *    even those not on the currently visible page.
 *
 * If you're using multiple listings and multiple BulkActions objects on
 * a single page, you'll want to adjust the $id of each BulkActions object
 * so they don't collide.
 */
class BulkActions
{
    /**
     * Constant to make params of fetchIdsFromListing() clearer when called.
     *
     * @const
     */
    const ENABLE_LISTING_PAGINATION = true;

    /**
     * Constant to make params of fetchIdsFromListing() clearer when called.
     *
     * @const
     */
    const DISABLE_LISTING_PAGINATION = false;

    /**
     * The ID used for the inputs rendered by this BulkActions object (e.g.
     * the checkboxes the user will use to select items).
     *
     * @var string
     */
    private $id = 'bulk_selections';

    /**
     * The Listing containing the items that will be selected.
     *
     * @var Listing
     */
    private $listing;

    /**
     * The Fields that will be used when fetching the data from the Listing.
     * Needed because the listing might alter its query based upon the fields.
     * For example, fields might be used to filter or sort the listing, which
     * could impact the BulkActions if we're doing the "select all, not just
     * visible page mode".
     *
     * @var Fields
     */
    private $fields;

    /**
     * Any actions (implementers of ActionInterface) registered/added.
     *
     * @var array
     */
    private $actions = [];

    /**
     * The items selected from the Listing.  Stored so we can check items
     * when re-rendering due to a validation error, for example.  Resets
     * each time process() is called.
     *
     * @var array
     */
    private $selected = [];

    /**
     * Provide the necessary Listing and Fields objects.
     *
     * @param Listing $listing
     * @param Fields $fields
     */
    public function __construct(Listing $listing, Fields $fields)
    {
        $this->listing = $listing;
        $this->fields  = $fields;
    }

    /**
     * Set the ID that should be used for inputs rendered by this object.
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
     * Get the ID used for inputs rendered by this object and related classes.
     * For example, the BulkActions ID will be used by the BulkActionsForm
     * view helper to set an ID for the "check all" control.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the Request used by the Listing.  Method provided here so we can hide
     * the Listing itself from actions, etc., while still providing access to the
     * HTTP request.
     *
     * @return \Dewdrop\Request
     */
    public function getRequest()
    {
        return $this->listing->getRequest();
    }

    /**
     * Get the primary key field from the Listing.
     *
     * @return \Dewdrop\Db\Field
     */
    public function getPrimaryKey()
    {
        return $this->listing->getPrimaryKey();
    }

    /**
     * Get an array of selected items.  Populated during process().
     *
     * @return array
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * Add an action.  Any implementor of ActionInterface is accepted.
     *
     * @param ActionInterface $action
     * @return $this
     */
    public function add(ActionInterface $action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Shortcut for adding a "button" action with a simple, single button that
     * triggers a callback to process the selected items.  This is a convenient
     * way to handle typical bulk actions without having to implement
     * ActionInterface on your own.
     *
     * @param string $id
     * @param string $title
     * @param callable $callback
     * @return BulkActions
     */
    public function addButton($id, $title, callable $callback)
    {
        $button = new Button($this);

        $button
            ->setId($id)
            ->setButtonTitle($title)
            ->setCallback($callback);

        return $this->add($button);
    }

    /**
     * Get all the actions that have been added.  Primary use of this method
     * would be to iterate over the actions and render their controls (e.g. in
     * BulkActionsForm view helper).
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Process our actions.  We only process if at least one item is selected
     * and an action's shouldProcess() returns true.  If we've processed an
     * action, a Result object is returned.  Otherwise, we return false.  The
     * Result object can be used to help with messaging and redirects in
     * whatever controller is orchestrating usage of these BulkActions.
     *
     * @return bool|Result
     */
    public function process()
    {
        $this->selected = $this->getSelectedItemsFromRequest();

        $result = false;

        if (count($this->selected)) {
            /* @var $action ActionInterface */
            foreach ($this->actions as $action) {
                if ($action->shouldProcess()) {
                    $result = $action->process($this->selected);

                    if (!$result instanceof Result) {
                        $result = new Result(Result::SUCCESS, 'Successfully processed bulk action.');
                    }

                    break;
                }
            }

        }

        return $result;
    }

    /**
     * Looking at the HTTP request, determine which items have been selected.
     * If we don't have an array of items, we return an empty array to maintain
     * consistency in our return data type.  If the request contains the
     * "check_pages" input for these bulk actions, we retrieve a full data set
     * from the Listing and return all the IDs.  Otherwise, we return an array
     * of the explicitly selected items.  In each case, we ensure that the items
     * legitimate records IDs from the Listing by comparing against the results
     * of Listing->fetchData().  This means you can be sure the IDs supplied to
     * your action's process() method are actual records in the DB that the user
     * was allowed to see/select.
     *
     * @return array
     */
    public function getSelectedItemsFromRequest()
    {
        $input = $this->getRequest()->getPost($this->id);

        if (!is_array($input)) {
            return [];
        } elseif ($this->getRequest()->getPost($this->id . '_check_pages')) {
            return $this->fetchIdsFromListing(self::DISABLE_LISTING_PAGINATION);
        } else {
            $valid = $this->fetchIdsFromListing(self::ENABLE_LISTING_PAGINATION);
            $clean = [];

            foreach ($valid as $validValue) {
                if (in_array($validValue, $input)) {
                    $clean[] = $validValue;
                }
            }

            return $clean;
        }
    }

    /**
     * Using the Listing's fetchData() method, grab an array of IDs.  Depending upon
     * the selection mode the user has chosen, we may or may not paginate the result
     * set.
     *
     * @param bool $usePagination
     * @return array
     */
    private function fetchIdsFromListing($usePagination)
    {
        $out = [];

        if (!$usePagination) {
            $pagination = $this->listing->getSelectModifierByName('SelectPaginate');

            $this->listing->removeSelectModifierByName('SelectPaginate');
        }

        foreach ($this->listing->fetchData($this->fields) as $row) {
            $out[] = $row[$this->listing->getPrimaryKey()->getName()];
        }

        if (!$usePagination && isset($pagination)) {
            $this->listing->registerSelectModifier($pagination);
        }

        return $out;
    }
}
