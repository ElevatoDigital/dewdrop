<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper\CsvCell;

use DateTimeZone;
use Dewdrop\Db\Field as DbField;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\HelperAbstract;
use Dewdrop\Fields\Helper\CellRenderer\ContentHelperInterface;

/**
 * The header helper allows you to render the content of the header for
 * a given field in a CSV table.  When defining
 * a custom callback for this helper, use the following callback
 * parameters:
 *
 * <pre>
 * $csvCell->getContentRenderer()->assign(
 *     'my_model:my_field',
 *     function ($helper, array $rowData, $rowIndex, $columnIndex) {
 *         // Param $helper is a reference to this instance of the Header helper
 *         // Param $rowData is an associative array representing all the data available to render this table row.
 *         // Param $rowIndex is a zero-based index of the current row being rendered
 *         // Param $columnIndex is a zero-based index of the current column being rendered
 *
 *         return 'something';
 *     }
 * );
 * </pre>
 */
class Content extends HelperAbstract implements ContentHelperInterface
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'csvcell.content';

    /**
     * It's possible to assign callbacks based upon column position/index.  This
     * can be useful if you want to automatically add some controls or other
     * content to the first column, for example.
     *
     * @var array
     */
    protected $callbacksByColumnPosition = array();

    /**
     * This content will be returned if any field's callback generates no output.
     *
     * @var string
     */
    protected $nullContentPlaceholder = '';

    /**
     * The default format for rendering dates.  Uses PHP's date() syntax.
     *
     * @var string
     */
    protected $dateFormat = 'M j, Y';

    /**
     * The default format for rendering time.  Uses PHP's date() syntax.
     *
     * @var string
     */
    protected $timeFormat = 'g:iA';

    /**
     * Set the content that should be returned if a field's callback returns no
     * output.
     *
     * @param string $nullContentPlaceholder
     * @return Content
     */
    public function setNullContentPlaceholder($nullContentPlaceholder)
    {
        $this->nullContentPlaceholder = $nullContentPlaceholder;

        return $this;
    }

    /**
     * Set an alternative format for date rendering.  Uses PHP's date() syntax.
     *
     * @param string $dateFormat
     * @return Content
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    /**
     * Set an alternative format for time rendering.  Uses PHP's date() syntax.
     *
     * @param string $timeFormat
     * @return \Dewdrop\Fields\Helper\TableCell\Content
     */
    public function setTimeFormat($timeFormat)
    {
        $this->timeFormat = $timeFormat;

        return $this;
    }

    /**
     * Render the cell's content for the supplied field.
     *
     * @param FieldInterface $field
     * @param array $rowData
     * @param int $rowIndex
     * @param int $columnIndex
     * @return string
     */
    public function render(FieldInterface $field, array $rowData, $rowIndex, $columnIndex)
    {
        if (array_key_exists($columnIndex, $this->callbacksByColumnPosition)) {
            $callable = $this->callbacksByColumnPosition[$columnIndex];
        } else {
            $callable = $this->getFieldAssignment($field);
        }

        $output = call_user_func($callable, $rowData, $rowIndex, $columnIndex);

        if (null === $output) {
            return $this->nullContentPlaceholder;
        } else {
            return $output;
        }
    }

    /**
     * Assign a callback based upon the column position currently being
     * displayed.  This can be useful, for example, if you'd like to display
     * something specific in a particular column of your table automatically.
     * You can wrap the callback that would have been used by calling
     * the getFieldAssignment() method with the appropriate field object.
     *
     * @param integer $columnIndex
     * @param callable $callback
     * @return Content
     */
    public function assignCallbackByColumnPosition($columnIndex, callable $callback)
    {
        $this->callbacksByColumnPosition[$columnIndex] = $this->wrapCallable($callback);

        return $this;
    }

    /**
     * If no custom callback is defined for a field, it will fall back to this
     * method to find a suitable callback.  In the case of the Content helper,
     * we only provide a fall back for DB-based fields.  Custom fields will have
     * to define a callback in order to function properly.
     *
     * @param FieldInterface $field
     * @return string|false
     */
    public function detectCallableForField(FieldInterface $field)
    {
        $method = null;

        if (!$field instanceof DbField) {
            return false;
        }

        if ($field->isType('boolean')) {
            $method = 'renderDbBoolean';
        } elseif ($field->isType('reference')) {
            $method = 'renderDbReference';
        } elseif ($field->isType('date')) {
            $method = 'renderDbDate';
        } elseif ($field->isType('timestamp')) {
            $method = 'renderDbTimestamp';
        } elseif ($field->isType('manytomany', 'clob', 'string', 'numeric')) {
            $method = 'renderDbText';
        } else {
            return false;
        }

        return function ($helper, array $rowData) use ($field, $method) {
            return $this->$method($field, $rowData);
        };
    }

    /**
     * A fall back method for basic DB fields.  Just returns the text
     * for the field in your row's data.
     *
     * @param DbField $field
     * @param array $rowData
     * @return string
     */
    protected function renderDbText(DbField $field, array $rowData)
    {
        return $rowData[$field->getName()];
    }

    /**
     * A fall back method for DB reference fields.  For foreign keys, we trim
     * the "_id" off the end and look for the resulting field in the result set.
     * For example, if you foreign key is "state_id", we look for a result set
     * key of "state" and render that value for the field.
     *
     * @param DbField $field
     * @param array $rowData
     * @return string
     */
    protected function renderDbReference(DbField $field, array $rowData)
    {
        return $rowData[preg_replace('/_id$/', '', $field->getName())];
    }

    /**
     * Render cell content for a boolean database field.
     *
     * @param DbField $field
     * @param array $rowData
     * @return string
     */
    protected function renderDbBoolean(DbField $field, array $rowData)
    {
        return $rowData[$field->getName()] ? 'Yes' : 'No';
    }

    /**
     * A fall back method for date fields.  Will convert the DB value to a
     * Unix timestamp and then format it with PHP's date() function.  (How
     * retro!)  You can customize the format with setDateFormat().
     *
     * @param DbField $field
     * @param array $rowData
     * @return string
     */
    protected function renderDbDate(DbField $field, array $rowData)
    {
        $value     = $rowData[$field->getName()];
        $timestamp = strtotime($value);

        // Hack for handling GMT offsets in WordPress.
        if (function_exists('get_option')) {
            $timezoneString = get_option('timezone_string');

            if ($timezoneString) {
                $offset = timezone_offset_get(new DateTimeZone($timezoneString), date_create($value));
                $timestamp += $offset;
            }
        }

        if ($timestamp) {
            return date($this->dateFormat, $timestamp);
        } else {
            return '';
        }
    }

    /**
     * A fall back method for timestamp fields.  Will convert the DB value to a
     * Unix timestamp and then format it with PHP's date() function.  (How
     * retro!)  You can customize the format with setDateFormat().
     *
     * @param DbField $field
     * @param array $rowData
     * @return string
     */
    protected function renderDbTimestamp(DbField $field, array $rowData)
    {
        $value     = $rowData[$field->getName()];
        $timestamp = strtotime($value);

        // Hack for handling GMT offsets in WordPress.
        if (function_exists('get_option')) {
            $timezoneString = get_option('timezone_string');

            if ($timezoneString) {
                $offset = timezone_offset_get(new DateTimeZone($timezoneString), date_create($value));
                $timestamp += $offset;
            }
        }

        if ($timestamp) {
            return date($this->dateFormat . ' ' . $this->timeFormat, $timestamp);
        } else {
            return '';
        }
    }
}
