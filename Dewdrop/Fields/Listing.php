<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Db\Select;
use Dewdrop\Fields;
use Dewdrop\Fields\Exception;
use Dewdrop\Fields\Helper\SelectCallback;
use Dewdrop\Fields\Helper\SelectFilter;
use Dewdrop\Fields\Helper\SelectModifierInterface;
use Dewdrop\Fields\Helper\SelectPaginate;
use Dewdrop\Fields\Helper\SelectSort;
use Dewdrop\Pimple;
use Dewdrop\Request;

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
     * A field that can be used to look up single items in this listing's
     * Select object.
     *
     * @var DbField
     */
    private $primaryKey;

    /**
     * The total number of rows that would have been fetched had no LIMIT
     * clause been present on the Select object.  This is calculated when
     * you call fetchData(), assuming you have a SelectPaginate helper
     * registered.
     *
     * @var int
     */
    private $totalRowCount = 0;

    /**
     * The Request object used by this listing and its select modifiers.
     *
     * @var Request
     */
    private $request;

    /**
     * Supply the Select object that will be manipulated by this listing.
     *
     * @param Select $select
     * @param DbField $primaryKey
     * @param Request $request
     */
    public function __construct(Select $select, DbField $primaryKey, Request $request = null)
    {
        $this->select     = $select;
        $this->primaryKey = $primaryKey;

        $this->request = ($request ?: Pimple::getResource('dewdrop-request'));

        $this
            ->registerSelectModifier(new SelectFilter($this->request))
            ->registerSelectModifier(new SelectSort($this->request))
            ->registerSelectModifier(new SelectPaginate($this->request));
    }

    /**
     * Retrieve this listing's primary key field.  Sometimes useful when
     * assembling URLs, etc.
     *
     * @return DbField
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
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
        /* @var $modifier SelectModifierInterface */
        foreach ($this->selectModifiers as $modifier) {
            $modifier->setPrefix($prefix);
        }

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
     * Return the request being used by this listing.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Allow users to create a custom modifier that just uses a callback
     * to modify the select object.
     *
     * @param $name
     * @param callable $callback
     * @return $this
     */
    public function registerCustomModifier($name, callable $callback)
    {
        $modifier = new SelectCallback();

        $modifier
            ->setName($name)
            ->setCallback($callback);

        $this->registerSelectModifier($modifier);

        return $this;
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

        $selectModifier->setPrefix($this->prefix);

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
     * Retrieve a select modifier using its name.
     *
     * @param string $name
     * @return SelectModifierInterface|false
     */
    public function getSelectModifierByName($name)
    {
        /* @var $modifier SelectModifierInterface */
        foreach ($this->selectModifiers as $modifier) {
            if ($modifier->matchesName($name)) {
                return $modifier;
            }
        }

        return false;
    }

    /**
     * Check to see if this listing has a select modifier with the given name.
     *
     * @param string $name
     * @return bool
     */
    public function hasSelectModifier($name)
    {
        /* @var $modifier SelectModifierInterface */
        foreach ($this->selectModifiers as $modifier) {
            if ($modifier->matchesName($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove the select modifier that matches the supplied name.
     *
     * @param string $name
     * @return $this
     */
    public function removeSelectModifierByName($name)
    {
        /* @var $modifier SelectModifierInterface */
        foreach ($this->selectModifiers as $index => $modifier) {
            if ($modifier->matchesName($name)) {
                unset($this->selectModifiers[$index]);
                break;
            }
        }

        return $this;
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
        /** @var $select Select */
        $select = clone($this->select);

        /* @var $modifier SelectModifierInterface */
        foreach ($this->selectModifiers as $modifier) {
            $select = $modifier->modifySelect($fields, $select, $this->prefix);

            if (!$select instanceof Select) {
                throw new Exception('Modifier did not return a Select object.');
            }
        }

        return $select;
    }

    /**
     * Fetch the data for this listing, passing the supplied \Dewdrop\Fields
     * object to all modifiers before fetching the data from the DB.
     *
     * @param Fields $fields
     * @return array
     * @throws
     */
    public function fetchData(Fields $fields)
    {
        $adapter = $this->select->getAdapter();
        $data    = $adapter->fetchAll($this->getModifiedSelect($fields));

        // Ensure we always return an array
        if (!$data) {
            $data = array();
        }

        if ($this->getSelectModifierByName('selectpaginate')) {
            $this->totalRowCount = $adapter->getDriver()->fetchTotalRowCount($data);
        }

        return $data;
    }

    /**
     * Fetch the data for this listing, passing the supplied \Dewdrop\Fields object to all modifiers before fetching the
     * data from the DB using a PHP generator.
     *
     * @param Fields $fields
     * @return \Generator
     */
    public function fetchDataWithGenerator(Fields $fields)
    {
        $adapter = $this->select->getAdapter();

        $this->totalRowCount = 0;

        foreach ($adapter->fetchAllWithGenerator($this->getModifiedSelect($fields)) as $row) {
            $this->totalRowCount++;
            yield $row;
        }
    }

    /**
     * Return the total row count that would have been retrieved during
     * fetchData() if no LIMIT clause was applied to the Select.  Note that this
     * will only work if you have a SelectPaginate select modifier registered
     * with this listing.
     *
     * @return integer
     */
    public function getTotalRowCount()
    {
        return $this->totalRowCount;
    }

    /**
     * Fetch a single row from this listing by using the supplied ID value to
     * match against the listing's primary key field.
     *
     * @param Fields $fields
     * @param mixed $id
     * @return \Dewdrop\Db\Row
     */
    public function fetchRow(Fields $fields, $id)
    {
        $select = $this->getModifiedSelect($fields);

        $quotedPrimaryKey = $select->quoteWithAlias(
            $this->primaryKey->getTable()->getTableName(),
            $this->primaryKey->getName()
        );

        $select->where("{$quotedPrimaryKey} = ?", $id);

        return $this->select->getAdapter()->fetchRow($select);
    }
}
