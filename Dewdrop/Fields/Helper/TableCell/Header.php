<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper\TableCell;

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
 * $tableCell->getHeaderRenderer()->assign(
 *     'my_model:my_field',
 *     function ($helper, $field) {
 *         // Param $helper is a reference to this instance of the Header helper
 *         // Param $field is your Field object, so you can get the label, etc.
 *
 *         return $helper->getEscaper()->escapeHtml($field->getLabel());
 *     }
 * );
 * </code>
 *
 * NOTE: You do not have to supply a callback for this helper for any fields.
 * By default, this helper will just use the field's label for the header
 * content, so you only need to supply a custom callback if the field label
 * is not appropriate.
 */
class Header extends HelperAbstract
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'tablecell.header';

    /**
     * A \Zend\Escaper\Escaper object used to escape content from your callbacks
     * to prevent XSS attacks.  You are responsible for escaping unsafe content.
     *
     * @var \Zend\Escaper\Escaper
     */
    private $escaper;

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
     * we fall back to all field's just returning their label.
     *
     * @param FieldInterface $field
     * @return callable
     */
    public function detectCallableForField(FieldInterface $field)
    {
        return function () use ($field) {
            return $this->escaper->escapeHtml($field->getLabel());
        };
    }
}
