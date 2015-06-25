<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Test;

use Dewdrop\Db\Select;
use Dewdrop\Fields;
use Dewdrop\Fields\Exception;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\SelectSort;
use Dewdrop\Fields\Listing;
use Dewdrop\Request;
use Exception as CorePhpException;
use PHPUnit_Framework_TestCase as PHPUnit;

/**
 * This utility can test the sorting capabilities of a Listing and
 * Fields object.  It will iterate over each sortable field and attempt
 * to fetch data from the Listing after you sort in both ascending and
 * descending order.  You can also use this utility in the context
 * of a PHPUnit test case to automate the testing of your Listing's
 * sorting.
 */
class ListingSort
{
    /**
     * The SelectSort helper used to sort the Listing's Select.
     *
     * @var SelectSort
     */
    private $sortHelper;

    /**
     * Provide the Fields and Listing objects that will be tested.
     *
     * @param Fields $fields
     * @param Listing $listing
     * @throws Exception
     */
    public function __construct(Fields $fields, Listing $listing)
    {
        $this->fields  = $fields->getSortableFields();
        $this->listing = $listing;

        $this->testSortHelperPresence();
    }

    /**
     * Ensure the sort helper is present on the Listing.
     *
     * @throws Exception
     */
    public function testSortHelperPresence()
    {
        foreach ($this->listing->getSelectModifiers() as $modifier) {
            if ($modifier instanceof SelectSort) {
                $this->sortHelper = $modifier;
                break;
            }
        }

        if (!$this->sortHelper) {
            throw new Exception('SelectSort helper must be available on Listing to test sorts.');
        }
    }

    /**
     * Sort the listing in both ascending and descending order for the
     * supplied field, catching any exceptions if the sorted query causes
     * a problem.
     *
     * @param FieldInterface $field
     * @return array
     */
    public function runOnSingleField(FieldInterface $field)
    {
        $directions = ['ASC', 'DESC'];
        $results    = [];

        foreach ($directions as $direction) {
            $results[$direction] = array('success' => false, 'message' => null, 'sql' => '');

            $request = new Request(
                array(),
                array(
                    $this->listing->getPrefix() . 'sort' => urldecode($field->getQueryStringId()),
                    $this->listing->getPrefix() . 'dir'  => $direction
                )
            );

            $this->sortHelper->setRequest($request);

            try {
                // Don't need much data here, just need to run the query.
                $select = $this->listing->getModifiedSelect($this->fields)->limit(1);

                $results[$direction]['sql'] = (string) $select->reset(Select::LIMIT_COUNT);

                $select->getAdapter()->fetchAll($select);

                $results[$direction]['success'] = true;
            } catch (CorePhpException $e) {
                $results[$direction]['message'] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Run tests on all the available fields.
     *
     * @return array
     */
    public function run()
    {
        $results = array();

        foreach ($this->fields as $field) {
            $results[$field->getId()] = $this->runOnSingleField($field);
        }

        return $results;
    }

    /**
     * Run the tests on all available fields and communicate the results to
     * PHPUnit using assertions.
     *
     * @param PHPUnit $phpUnit
     */
    public function runInPhpUnit(PHPUnit $phpUnit)
    {
        foreach ($this->run() as $id => $results) {
            $phpUnit->assertTrue($results['ASC']['success'], $results['ASC']['message']);
            $phpUnit->assertTrue($results['DESC']['success'], $results['DESC']['message']);
        }
    }
}
