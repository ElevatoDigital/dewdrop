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
 * Render an image picker control that uses the default WordPress media
 * upload/management dialog and sets the value of a hidden input.
 */
class WpImagePicker extends AbstractHelper
{
    /**
     * Render using a method appropriate for the supplied arguments.
     *
     * @return string
     */
    public function direct()
    {
        return $this->delegateByArgs(func_get_args(), 'direct');
    }

    /**
     * Render the image picker using the supplied Field object.
     *
     * @param Field $field
     * @return string
     */
    public function directField(Field $field)
    {
        return $this->directArray(
            array(
                'name'  => $field->getControlName(),
                'id'    => $field->getHtmlId(),
                'value' => $field->getValue()
            )
        );
    }

    /**
     * Render an image picker using the explicitly provided arguments.
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    public function directExplicit($name, $value)
    {
        return $this->directArray(
            array(
                'name'  => $name,
                'value' => $value
            )
        );
    }

    /**
     * Render the image picker using the key-value arguments in the supplied
     * $options array.
     *
     * @param array $options
     * @return string
     */
    public function directArray(array $options)
    {
        extract($this->prepareOptionsArray($options));

        if (null === $id) {
            $id = $name;
        }

        wp_enqueue_media();

        wp_enqueue_script(
            'dewdrop-image-uploader',
            plugins_url('www-wp/js/image-uploader.js', __FILE__),
            array('media-upload'),
            false,
            true
        );

        return $this->partial(
            'wp-image-picker.phtml',
            array(
                'name'    => $name,
                'id'      => $id,
                'value'   => $value
            )
        );
    }

    /**
     * Ensure the supplied options array have the required items.
     *
     * @param array $options
     * @return array
     */
    private function prepareOptionsArray(array $options)
    {
        $this
            ->checkRequired($options, array('name', 'value'))
            ->ensurePresent($options, array('id'));

        return $options;
    }
}
