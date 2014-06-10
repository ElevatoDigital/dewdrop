<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Exception;
use Dewdrop\Fields;
use Dewdrop\Fields\RowLinker;
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

        $fields    = $args[0];
        $rowLinker = $args[1];
        $renderer  = (isset($args[2]) && $args[2] instanceof Renderer ? $args[2] : $this->view->editControlRenderer());

        return $this->open()
            . $this->renderFields($fields, $rowLinker, $renderer)
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

    public function renderFields(Fields $fields, RowLinker $rowLinker, Renderer $renderer)
    {
        $output = '';

        // Track where in the form each field is rendered.  Mainly for autofocus.
        $fieldPosition = 0;

        foreach ($fields->getEditableFields() as $field) {
            $output .= '<div class="row">';

            $output .= sprintf(
                '<div class="form-group col-lg-6 col-md-6 col-sm-8%s">',
                ($rowLinker->hasErrors($field) ? ' has-feedback has-error' : '')
            );

            $controlOutput = $renderer->getControlRenderer()->render($field, $fieldPosition);

            // If the control renders a label itself and isn't a list, skip it here
            if (false === stripos($controlOutput, '<label') || false !== stripos($controlOutput, '<ul')) {
                $output .= sprintf(
                    '<label for="%s">%s</label>',
                    $this->view->escapeHtmlAttr($field->getHtmlId()),
                    $renderer->getLabelRenderer()->render($field)
                );
            }

            $output .= $controlOutput;

            if ($rowLinker->hasErrors($field)) {
                foreach ($rowLinker->getErrorMessages($field) as $message) {
                    $output .= sprintf(
                        '<div class="help-block">%s</div>',
                        $this->view->escapeHtml($message)
                    );
                }
            }

            if ($field->getNote()) {
                $output .= sprintf(
                    '<div class="help-block">%s</div>',
                    $this->view->escapeHtml($field->getNote())
                );
            }

            $output .= '</div>';
            $output .= '</div>';

            $fieldPosition += 1;
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
