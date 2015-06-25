<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Field;

class CascadeSelect extends AbstractHelper
{
    public function direct(Field $field, Field $cascadeFrom)
    {
        $this->view->headScript()->appendFile($this->view->bowerUrl('/dewdrop/www/js/cascade-select.js'));

        return $this->view->select(
            [
                'name'       => $field->getControlName(),
                'id'         => $field->getHtmlId(),
                'options'    => [],
                'value'      => $field->getValue(),
                'attributes' => [
                    'data-cascade-options' => $this->view->encodeJsonHtmlSafe(
                        $field->getOptionGroups()->fetchJsonWrapper()
                    ),
                    'data-cascade-from'    => '#' . $cascadeFrom->getHtmlId(),
                    'data-cascade-title'   => "Choose a {$cascadeFrom->getLabel()}...",
                    'data-show-blank'      => true,
                    'data-blank-title'     => '',
                    'data-value'           => $field->getValue()
                ]
            ]
        );
    }
}
