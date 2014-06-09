<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Exception;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\EditControl as Renderer;

class BootstrapForm extends AbstractHelper
{
    public function direct()
    {
        $args = func_get_args();

        if (0 === $args) {
            return $this;
        }

        if (!$args[0] instanceof Fields) {
            throw new Exception('BootstrapForm takes either no arguments or a Fields object');
        }

        $fields   = $args[0];
        $renderer = (isset($args[1]) && $args[1] instanceof Renderer ? $args[1] : $this->view->editControlRenderer());
        $action   = '';

        if (isset($args[2])) {
            $action = $args[2];
        }

        return $this->open($action)
            . $this->renderFields($fields, $renderer)
            . $this->renderSubmitButton()
            . $this->close();
    }

    public function open($action = '', $method = 'POST')
    {
        return sprintf(
            '<form role="form" action="%s" method="%s">',
            $this->view->escapeHtmlAttr($action),
            $this->view->escapeHtmlAttr($method)
        );
    }

    public function close()
    {
        return '</form>';
    }

    public function renderFields(Fields $fields, Renderer $renderer)
    {
        $output = '';

        foreach ($fields->getEditableFields() as $field) {
            $output .= '<div class="row">';
            $output .= '<div class="col-lg-6 col-md-6 col-sm-8 form-group">';

            $controlOutput = $renderer->getControlRenderer()->render($field);

            // If the control renders a label itself and isn't a list, skip it here
            if (false === stripos($controlOutput, '<label') || false !== stripos($controlOutput, '<ul')) {
                $output .= sprintf(
                    '<label for="%s">%s</label>',
                    $this->view->escapeHtmlAttr($field->getHtmlId()),
                    $renderer->getLabelRenderer()->render($field)
                );
            }

            $output .= $controlOutput;

            $output .= '</div>';
            $output .= '</div>';
        }

        return $output;
    }

    public function renderSubmitButton($title = 'Save Changes')
    {
        return sprintf(
            '<div class="form-group"><input type="submit" value="%s" class="btn btn-primary" /></div>',
            $this->view->escapeHtmlAttr($title)
        );
    }
}
