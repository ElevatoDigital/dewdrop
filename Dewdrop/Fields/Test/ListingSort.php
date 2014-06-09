<?php

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

class ListingSort
{
    private $sortHelper;

    public function __construct(Listing $listing, Fields $fields)
    {
        $this->listing = $listing;
        $this->fields  = $fields->getSortableFields();

        $this->testSortHelperPresence();
    }

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

    public function runOnSingleField(FieldInterface $field)
    {
        $directions = array('ASC', 'DESC');

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

                $data = $select->getAdapter()->fetchAll($select);

                $results[$direction]['success'] = true;
            } catch (CorePhpException $e) {
                $results[$direction]['message'] = $e->getMessage();
            }
        }

        return $results;
    }

    public function run()
    {
        $results = array();

        foreach ($this->fields as $field) {
            $results[$field->getId()] = $this->runOnSingleField($field);
        }

        return $results;
    }

    public function runInPhpUnit(PHPUnit $phpUnit)
    {
        foreach ($this->run() as $id => $results) {
            $phpUnit->assertTrue($results['ASC']['success'], $results['ASC']['message']);
            $phpUnit->assertTrue($results['DESC']['success'], $results['DESC']['message']);
        }
    }
}
