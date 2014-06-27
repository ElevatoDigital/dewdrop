<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Field;

/**
 * Render a basic HTML &lt;select&gt; element using the supplied options.
 */
class Select extends AbstractHelper
{
    /**
     * Render the <select>.
     *
     * This method will delegate to directField(), directExplicit(), or
     * directArray() depending upon the arguments that are passed to it.
     *
     * @return string
     */
    public function direct()
    {
        return $this->delegateByArgs(func_get_args(), 'direct');
    }

    /**
     * Use a \Dewdrop\Db\Field object to render the select tag.  The OptionPairs
     * API will be used to retrieve the actual options allowed for this control.
     *
     * @param Field $field
     * @return string
     */
    public function directField(Field $field)
    {
        return $this->directArray(
            array(
                'name'    => $field->getControlName(),
                'id'      => $field->getHtmlId(),
                'options' => $field->getOptionPairs()->fetch(),
                'value'   => $field->getValue()
            )
        );
    }

    /**
     * Specify the basic options available for this view helper explicitly, as
     * basic PHP args.
     *
     * @param string $name The name attribute for the <select>.
     * @param array $options The key-value pairs representing the select options.
     * @param mixed $value The selected value.
     * @param mixed $classes Any CSS classes you'd like to add.
     * @return string
     */
    public function directExplicit($name, array $options, $value, $classes = null)
    {
        if (null !== $classes && !is_array($classes)) {
            $classes = array($classes);
        }

        return $this->directArray(
            array(
                'name'    => $name,
                'value'   => $value,
                'options' => $options,
                'classes' => $classes
            )
        );
    }

    /**
     * Render the <select> using an array of name-value options.
     *
     * @param array $options
     * @return string
     */
    public function directArray(array $options)
    {
        extract($this->prepareOptionsArray($options));

        if (!isset($showBlank)) {
            $showBlank = true;
        }

        if (null === $id) {
            $id = $name;
        }

        if (0 === count($classes)) {
            $classes[] = 'form-control';
        }

        $value = (string) $value;

        return $this->partial(
            'select.phtml',
            array(
                'name'       => $name,
                'id'         => $id,
                'value'      => $value,
                'options'    => $options,
                'classes'    => $classes,
                'blankTitle' => $blankTitle,
                'showBlank'  => $showBlank
            )
        );
    }

    /**
     * Prepare the options array for the directArray() method, checking that
     * required options are set.
     *
     * @param array $options
     * @return array
     */
    private function prepareOptionsArray(array $options)
    {
        $this
            ->checkRequired($options, array('name', 'value', 'options'))
            ->ensurePresent($options, array('id', 'classes', 'blankTitle'))
            ->ensureArray($options, array('options', 'classes'));

        return $options;
    }
}
