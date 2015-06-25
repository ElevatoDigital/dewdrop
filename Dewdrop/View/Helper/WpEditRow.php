<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Field;

/**
 * Render a row inside the WP admin's edit form table.
 *
 * This helper can leverage information from a \Dewdrop\Db\Field object to
 * set it's options prior to rendering.
 *
 * Example usage:
 *
 * <pre>
 * echo $this->wpEditRow()->open($this->fields->get('model:field_name'));
 * echo $this->wpInputText($this->fields->get('model:field_name'));
 * echo $this->wpEditRow()->close();
 * </pre>
 */
class WpEditRow extends AbstractHelper
{
    /**
     * Open the edit row.
     *
     * This method will delegate to openField(), openExplicit(), or openArray()
     * depening upon the arguments that are passed to it.
     *
     * @return string
     */
    public function open()
    {
        return $this->delegateByArgs(func_get_args(), 'open');
    }

    /**
     * Open the edit row using a \Dewdrop\Db\Field object to determine the
     * label text and the value of the "for" attribute on the label tag.
     *
     * @param Field $field
     * @return string
     */
    protected function openField(Field $field)
    {
        return $this->openArray(
            array(
                'label'     => $field->getLabel(),
                'labelFor'  => $field->getHtmlId(),
                'hideLabel' => $field->isType('boolean')
            )
        );
    }

    /**
     * Open the edit row using explicitly passed arguments for the
     * label text and the label tag's "for" attribute, if any.
     *
     * @param string $label
     * @param string $labelFor
     * @return string
     */
    protected function openExplicit($label, $labelFor = null)
    {
        return $this->openArray(
            array(
                'label'    => $label,
                'labelFor' => $labelFor
            )
        );
    }

    /**
     * Open the edit row using an array of key-value pairs to assign
     * the options.
     *
     * If the labelFor option is null, no label tag will be rendering,
     * instead rendering the label text unadorned.  This is useful and
     * necessary to emulate the WordPress style of rendering checkboxes
     * with a plain-text label in the left-hand column and then an actual
     * label tag in the right-hand column with the "for" assigned to the
     * checkbox node.
     *
     * @param array $options
     * @return string
     */
    protected function openArray(array $options)
    {
        extract($this->prepareOpenOptionsArray($options));

        return $this->partial(
            'wp-edit-row-open.phtml',
            array(
                'label'     => $label,
                'labelFor'  => $labelFor,
                'hideLabel' => $hideLabel
            )
        );
    }

    /**
     * Close the edit row.
     *
     * @return string
     */
    public function close()
    {
        return $this->delegateByArgs(func_get_args(), 'close');
    }

    /**
     * Use the supplied field to render the closing tags.  The field's
     * note will be used, if it is set.
     *
     * @param Field $field
     * @return string
     */
    public function closeField(Field $field)
    {
        return $this->closeArray(
            array(
                'note' => $field->getNote()
            )
        );
    }

    /**
     * Close the edit row, explicitly specifying the arguments, in this
     * case the note that will be displayed with the row.
     *
     * @param string $note
     * @return string
     */
    public function closeExplicit($note = '')
    {
        return $this->closeArray(
            array(
                'note' => $note
            )
        );
    }

    /**
     * Close edit row, specifying options as a name-value array.
     *
     * @param array $options
     * @return string
     */
    public function closeArray(array $options)
    {
        extract($this->prepareCloseOptionsArray($options));

        return $this->partial(
            'wp-edit-row-close.phtml',
            array(
                'note' => $note
            )
        );
    }

    /**
     * Check and prepare options for use in openArray()
     *
     * @param array $options
     * @return array
     */
    protected function prepareOpenOptionsArray(array $options)
    {
        $this
            ->checkRequired($options, array('label'))
            ->ensurePresent($options, array('labelFor', 'hideLabel'));

        return $options;
    }

    /**
     * Ensure the "note" option is present prior to rendering closing partial.
     *
     * @param array $options
     * @return $options
     */
    protected function prepareCloseOptionsArray(array $options)
    {
        $this->ensurePresent($options, array('note'));

        return $options;
    }
}
