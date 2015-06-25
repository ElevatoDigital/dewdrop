<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Db\Adapter;
use Dewdrop\Db\Select;
use Dewdrop\Exception as DewdropException;
use Dewdrop\Fields\OptionGroups\GroupColumnNotSetException;
use Dewdrop\Fields\OptionPairs\TitleColumnNotDetectedException;

/**
 * The OptionGroups class makes it easy to retrieve a list of key-value pairs
 * in arrays grouped by a key for use as options for a field's value, typically
 * a foreign key.  OptionGroups is ideal for use with a CascadeSelect, for
 * example.
 *
 * Typically, you'll just need to call setGroupColumn() to get OptionGroups
 * working.  If the auto-generated SQL statement isn't doing what you need, you
 * can call setSelect() to provide your own.  Just ensure its resultset returns
 * "title", "value" and "group" keys in its rows.
 */
class OptionGroups extends OptionPairs
{
    /**
     * Present so we can re-use customizations made to the OptionPairs object
     * on a field.
     *
     * @var OptionPairs
     */
    protected $optionPairs;

    /**
     * The name of the column by which the options should be grouped.
     *
     * @var string
     */
    protected $groupColumn;

    /**
     * Set an OptionPairs object we can look at to re-use customizations.
     *
     * @param OptionPairs $optionPairs
     * @return $this
     */
    public function setOptionPairs(OptionPairs $optionPairs)
    {
        $this->optionPairs = $optionPairs;

        return $this;
    }

    /**
     * Set the column by which options will be grouped.
     *
     * @param $groupColumn
     * @return $this
     */
    public function setGroupColumn($groupColumn)
    {
        $this->groupColumn = $groupColumn;

        return $this;
    }

    /**
     * Fetch the option groups using the DB adapter.
     *
     * @return array
     */
    public function fetch()
    {
        return $this->dbAdapter->fetchAllGroupedByKey($this->getSelect(), 'group');
    }

    /**
     * Wrap the option pairs in such a way that their sort order will be
     * maintained when encoded as JSON.  This method doesn't do the JSON
     * encoding itself, just makes it JSON encoding friendly.
     *
     * @return array
     */
    public function fetchJsonWrapper()
    {
        $options = $this->fetch();
        $output  = [];

        foreach ($options as $groupId => $groupOptions) {
            $optionPairs = [];

            foreach ($groupOptions as $option) {
                $optionPairs[$option['value']] = $option['title'];
            }

            $output[] = [
                'groupId' => $groupId,
                'options' => $this->formatJsonWrapper($optionPairs)
            ];
        }

        return $output;
    }

    /**
     * Ensure we retrieve the group column along with the others generating
     * a SQL statement.
     *
     * @throws GroupColumnNotSetException
     * @return array
     */
    protected function getSelectColumns()
    {
        if (!$this->groupColumn) {
            $meta = $this->loadTableMetadata();

            $exception = new GroupColumnNotSetException(
                'You must call setGroupColumn() before fetching from OptionGroups.'
            );

            $exception
                ->setTableName($this->tableName)
                ->setColumns($meta['columns']);

            throw $exception;
        }

        return [
            'group' => $this->groupColumn,
            'value' => $this->valueColumn,
            'title' => $this->titleColumn
        ];
    }

    /**
     * If no title column has been set on this OptionGroups object directly,
     * we attempt to get it from the OptionPairs object, if present.  This
     * allows a user to set a custom title column on OptionPairs and have it
     * automatically take effect on OptionGroups as well.  Otherwise, we'll
     * revert back to the standard auto-detection behavior.
     *
     * @throws TitleColumnNotDetectedException
     * @param array $columns The "columns" portion of the table metadata.
     * @return string
     */
    protected function findTitleColumnFromMetadata(array $columns)
    {
        if ($this->optionPairs->hasTitleColumn()) {
            return $this->optionPairs->getTitleColumn();
        } else {
            return parent::findTitleColumnFromMetadata($columns);
        }
    }
}
