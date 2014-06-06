<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper\TableCell;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\HelperAbstract;
use Zend\Escaper\Escaper;

/**
 * The header helper allows you to render the content of the header for
 * a given field in a table (typically the &lt;th&gt; tag).  When defining
 * a custom callback for this helper, using the following callback
 * parameters:
 *
 * <code>
 * $tableCell->getContentRenderer()->assign(
 *     'my_model:my_field',
 *     function ($helper, array $rowData, $rowIndex, $columnIndex) {
 *         // Param $helper is a reference to this instance of the Header helper
 *         // Param $rowData if an associative array representing all the data available to render this table row.
 *         // Param $rowIndex is a zero-based index of the current row being rendered
 *         // Param $columnIndex is a zero-based index of the current column being rendered
 *
 *         return '<strong>' . $helper->getEscaper()->escapeHtml($row['my_field']) . '</strong>;
 *     }
 * );
 * </code>
 */
class Content extends HelperAbstract
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'tablecell.content';

    /**
     * A \Zend\Escaper\Escaper object used to escape content from your callbacks
     * to prevent XSS attacks.  You are responsible for escaping unsafe content.
     *
     * @var \Zend\Escaper\Escaper
     */
    private $escaper;

    /**
     * The default format for rendering dates.  Uses PHP's date() syntax.
     *
     * @var string
     */
    private $dateFormat = 'M j, Y';

    /**
     * The default format for rendering time.  Uses PHP's date() syntax.
     *
     * @var string
     */
    private $timeFormat = 'g:iA';

    /**
     * Provide a \Zend\Escaper\Escaper that can be used by callbacks to escape
     * their output to prevent XSS attacks.
     *
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * Get the \Zend\Escaper\Escaper instance in your callbacks.
     *
     * @return \Zend\Escaper\Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * Set an alternative format for date rendering.  Uses PHP's date() syntax.
     *
     * @return \Dewdrop\Fields\Helper\TableCell\Content
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    /**
     * Set an alternative format for time rendering.  Uses PHP's date() syntax.
     *
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
        $callable = $this->getFieldAssignment($field);

        return call_user_func($callable, $rowData, $rowIndex, $columnIndex);
    }

    /**
     * If no custom callback is defined for a field, it will fall back to this
     * method to find a suitable callback.  In the case of the Content helper,
     * we only provide a fall back for DB-based fields.  Custom fields will have
     * to define a callback in order to function properly.
     *
     * @param FieldInterface $field
     * @return mixed
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
        }

        if (!$method) {
            return false;
        } else {
            return function ($helper, array $rowData) use ($field, $method) {
                return $this->$method($field, $rowData);
            };
        }
    }

    /**
     * A fall back method for basic DB fields.  Just returns the escaped text
     * for the field in your row's data.
     *
     * @param FieldInterface $field
     * @param array $rowData
     * @return string
     */
    protected function renderDbText(FieldInterface $field, array $rowData)
    {
        return $this->escaper->escapeHtml($rowData[$field->getName()]);
    }

    /**
     * A fall back method for date fields.  Will convert the DB value to a
     * Unix timestamp and then format it with PHP's date() function.  (How
     * retro!)  You can customize the format with setDateFormat().
     *
     * @param FieldInterface $field
     * @param array $rowData
     * @return string
     */
    protected function renderDbDate(FieldInterface $field, array $rowData)
    {
        $value     = $rowData[$field->getName()];
        $timestamp = strtotime($value);

        if ($timestamp) {
            return $this->escaper->escapeHtml(date($this->dateFormat, $timestamp));
        } else {
            return '';
        }
    }

    /**
     * A fall back method for timestamp fields.  Will convert the DB value to a
     * Unix timestamp and then format it with PHP's date() function.  (How
     * retro!)  You can customize the format with setDateFormat().
     *
     * @param FieldInterface $field
     * @param array $rowData
     * @return string
     */
    protected function renderDbTimestamp(FieldInterface $field, array $rowData)
    {
        $value     = $rowData[$field->getName()];
        $timestamp = strtotime($value);

        return $this->escaper->escapeHtml(date($this->dateFormat . ' ' . $this->timeFormat, $timestamp));
    }
}
