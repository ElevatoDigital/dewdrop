<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Db\Select;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\SelectModifierInterface;

/**
 * The Listing class wraps a Select object and applies a number of SelectModifier
 * objects to it, making it easy to sort, filter, etc.  You can optionally specify
 * a prefix for the Listing, which it will pass along to all its modifiers,
 * allowing you to use multiple Listings on a single request without their GET
 * and POST variables conflicting with one another.
 */
class Listing
{
    /**
     * The Select that will be used throughout this Listing.
     *
     * @var Select
     */
    private $select;

    /**
     * An optional prefix to use when reading/writing request parameters in
     * this listing and its modifiers.
     *
     * @var string
     */
    private $prefix = '';

    /**
     * An array of SelectModifier objects used to automatically modify the
     * Select object.
     *
     * @var array
     */
    private $selectModifiers = array();

    /**
     * Supply the Select object that will be manipulated by this listing.
     *
     * @param Select $select
     */
    public function __construct(Select $select)
    {
        $this->select = $select;
    }

    /**
     * Set a prefix for this listing and any of its modifiers.  This can
     * aid in avoiding GET/POST variable naming conflicts when including
     * multiple listings on a single page.
     *
     * @param string $prefix
     * @return \Dewdrop\Fields\Listing
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get the prefix that should be used for GET/POST params by this
     * Listing and its modifiers.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Register a new SelectModifierInterface object with this listing.  Each
     * modifier will get the opportunity to alter the Select prior to its being
     * run.
     *
     * @param SelectModifierInterface $selectModifier
     * @return \Dewdrop\Fields\Listing
     */
    public function registerSelectModifier(SelectModifierInterface $selectModifier)
    {
        $this->selectModifiers[] = $selectModifier;

        return $this;
    }

    /**
     * Get all the select modifiers that have been registered with this Listing.
     * Mostly useful during testing.
     *
     * @return array
     */
    public function getSelectModifiers()
    {
        return $this->selectModifiers;
    }

    /**
     * Get the Select object after all modifiers have been applied to it.  This
     * can be useful if you'd like to see the Select (or its resulting SQL code)
     * will all modifications applied, in case you need to debug or troubleshoot
     * the code.
     *
     * @param Fields $fields
     * @return Select
     */
    public function getModifiedSelect(Fields $fields)
    {
        $select = clone($this->select);

        foreach ($this->selectModifiers as $modifier) {
            $select = $modifier->modifySelect($fields, $select, $this->prefix);
        }

        return $select;
    }

    /**
     * Fetch the data for this listing, passing the supplied \Dewdrop\Fields
     * object to all modifiers before fetching the data from the DB.
     *
     * @return array
     */
    public function fetchData(Fields $fields)
    {
        return $this->select->getAdapter()->fetchAll($this->getModifiedSelect($fields));
    }
}
