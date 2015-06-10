<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper\CsvCell;

use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\CellRenderer\HeaderHelperInterface;
use Dewdrop\Fields\Helper\HelperAbstract;

/**
 * The header helper allows you to render the content of the header for
 * a given field in a CSV table.  When defining
 * a custom callback for this helper, using the following callback
 * parameters:
 *
 * <pre>
 * $csvCell->getHeaderRenderer()->assign(
 *     'my_model:my_field',
 *     function ($helper, $field) {
 *         // Param $helper is a reference to this instance of the Header helper
 *         // Param $field is your Field object, so you can get the label, etc.
 *
 *         return 'something';
 *     }
 * );
 * </pre>
 *
 * NOTE: You do not have to supply a callback for this helper for any fields.
 * By default, this helper will just use the field's label for the header
 * content, so you only need to supply a custom callback if the field label
 * is not appropriate.
 */
class Header extends HelperAbstract implements HeaderHelperInterface
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'csvcell.header';

    /**
     * Render the header content for the supplied field.
     *
     * @param FieldInterface $field
     * @return string
     */
    public function render(FieldInterface $field)
    {
        $callable = $this->getFieldAssignment($field);

        return call_user_func($callable);
    }

    /**
     * If no custom callback is defined for a field, it will fall back to this
     * method to find a suitable callback.  In the case of the Header helper,
     * we fall back to all fields just returning their labels.
     *
     * @param FieldInterface $field
     * @return callable
     */
    public function detectCallableForField(FieldInterface $field)
    {
        return function () use ($field) {
            return $field->getLabel();
        };
    }
}
