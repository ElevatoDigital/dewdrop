<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Field;

class WpImagePicker extends AbstractHelper
{
    public function direct()
    {
        return $this->delegateByArgs(func_get_args(), 'direct');
    }

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

    public function directExplicit($name, $value)
    {
        return $this->directArray(
            array(
                'name'  => $name,
                'value' => $value
            )
        );
    }

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

    private function prepareOptionsArray(array $options)
    {
        $this
            ->checkRequired($options, array('name', 'value'))
            ->ensurePresent($options, array('id'));

        return $options;
    }
}
