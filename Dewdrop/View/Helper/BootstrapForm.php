<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Exception;
use Dewdrop\Fields;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\GroupedFields;
use Dewdrop\Fields\Helper\EditControl as Renderer;
use Dewdrop\Pimple;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

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

        $fields      = $args[0];
        $inputFilter = $args[1];
        $renderer    = (isset($args[2]) && $args[2] instanceof Renderer ? $args[2] : $this->view->editControlRenderer());

        if ($fields instanceof GroupedFields && 1 < count($fields->getGroups())) {
            $renderMethod = 'renderGroupedFields';
        } else {
            $renderMethod = 'renderFields';
        }

        return $this->open()
            . $this->$renderMethod($fields, $inputFilter, $renderer)
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

    public function renderGroupedFields(GroupedFields $groupedFields, InputFilter $inputFilter, Renderer $renderer)
    {
        $output = '<ul class="nav nav-tabs">';

        foreach ($groupedFields->getGroups() as $index => $group) {
            if (count($group)) {
                $output .= sprintf(
                    '<li%s><a href="#group_%d" data-toggle="tab">%s</a></li>',
                    (0 === $index ? ' class="active"' : ''),
                    $index,
                    $this->view->escapeHtml($group->getTitle())
                );
            }
        }

        $output .= '</ul>';
        $output .= '<div class="tab-content">';

        foreach ($groupedFields->getGroups() as $index => $group) {
            if (count($group)) {
                $output .= sprintf(
                    '<div class="tab-pane-edit tab-pane fade%s" id="group_%d">',
                    (0 === $index ? ' in active' : ''),
                    $index
                );

                $output .= $this->renderFields($group, $inputFilter, $renderer);

                $output .= '</div>';
            }
        }

        $output .= '</div>';

        return $output;
    }

    public function renderFields(Fields $fields, InputFilter $inputFilter, Renderer $renderer)
    {
        $output = '';

        // Track where in the form each field is rendered.  Mainly for autofocus.
        $fieldPosition = 0;

        foreach ($fields->getEditableFields() as $field) {
            $output  .= '<div class="">';
            $input    = ($inputFilter->has($field->getId()) ? $inputFilter->get($field->getId()) : null);
            $messages = ($input ? $input->getMessages() : null);

            $output .= sprintf(
                $this->renderFormGroupOpenTag(),
                ($messages ? ' has-feedback has-error alert alert-danger' : '')
            );

            $controlOutput = $renderer->getControlRenderer()->render($field, $fieldPosition);

            if ($this->controlRequiresLabel($controlOutput)) {
                $output .= $this->renderLabel($renderer, $field, $input);
            }

            $output .= $controlOutput;

            if ($messages) {
                $output .= $this->renderMessages($messages);
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

    public function renderFormGroupOpenTag()
    {
        return '<div class="form-group%s">';
    }

    public function renderLabel(Renderer $renderer, FieldInterface $field, Input $input = null)
    {
        return sprintf(
            '<label class="control-label" for="%s">%s%s</label>',
            $this->view->escapeHtmlAttr($field->getHtmlId()),
            $renderer->getLabelRenderer()->render($field),
            ($input && !$input->allowEmpty() ? $this->renderRequiredFlag() : '')
        );
    }

    public function renderRequiredFlag()
    {
        return ' <small><span title="Required" class="glyphicon glyphicon-asterisk text-danger"></span></small>';
    }

    public function renderMessages($messages)
    {
        $output = '';

        foreach ($messages as $message) {
            $output .= sprintf(
                '<div class="help-block">%s</div>',
                $this->view->escapeHtml($message)
            );
        }

        return $output;
    }

    public function renderSubmitButton($title = 'Save Changes')
    {
        return sprintf(
            '<div class="form-group">
                <input type="submit" value="%s" class="btn btn-primary" />
            </div>',
            $this->view->escapeHtmlAttr($title)
        );
    }

    protected function controlRequiresLabel($output)
    {
        return false === stripos($output, '<label') || false !== stripos($output, '<ul');
    }
}
