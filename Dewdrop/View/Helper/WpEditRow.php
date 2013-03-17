<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Field;

class WpEditRow extends AbstractHelper
{
    public function open()
    {
        return $this->delegateByArgs(func_get_args(), 'open');
    }

    protected function openField(Field $field)
    {
        return $this->openArray(
            array(
                'label'    => $field->getLabel(),
                'labelFor' => $field->getControlName()
            )
        );
    }

    protected function openExplicit($label, $labelFor = null)
    {
        return $this->openArray(
            array(
                'label'    => $label,
                'labelFor' => $labelFor
            )
        );
    }

    protected function openArray(array $options)
    {
        extract($this->prepareOptionsArray($options));

        return $this->partial(
            'wp-edit-row-open.phtml',
            array(
                'label'    => $label,
                'labelFor' => $labelFor
            )
        );
    }

    public function close()
    {
        return $this->partial(
            'wp-edit-row-close.phtml',
            array(

            )
        );
    }

    protected function prepareOptionsArray($options)
    {
        $this
            ->checkRequired($options, array('label'))
            ->ensurePresent($options, array('labelFor'));

        return $options;
    }
}
