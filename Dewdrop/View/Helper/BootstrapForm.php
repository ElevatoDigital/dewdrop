<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Exception;
use Dewdrop\Fields;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\GroupedFields;
use Dewdrop\Fields\Helper\EditControl as Renderer;
use Dewdrop\Pimple;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Render an HTML form using Bootstrap classes and markup.  Note that this
 * view helper can also render a \Dewdrop\Fields\GroupedFields object in a tab
 * view.
 */
class BootstrapForm extends AbstractHelper
{
    /**
     * If the user supplies no arguments, we return immediately, assuming that
     * they want to call other methods on this helper directly.  Otherwise, we
     * pass execution along to the directWithArgs() method so that the arguments
     * can be validated.
     *
     * @return mixed
     */
    public function direct()
    {
        $args = func_get_args();

        if (0 === $args) {
            return $this;
        } else {
            return call_user_func_array(array($this, 'directWithArgs'), $args);
        }
    }

    /**
     * If the direct method was called with arguments, it delegates to this
     * method.  When a user supplies a \Dewdrop\Fields object, a
     * \Zend\InputFilter\InputFilter object and an optional TableCell field
     * helper as a renderer, the view helper can render the entire form in
     * a single step.
     *
     * @param Fields $fields
     * @param InputFilter $inputFiler
     * @param Renderer $renderer
     * @return string
     */
    public function directWithArgs(Fields $fields, InputFilter $inputFilter, Renderer $renderer = null)
    {
        $renderer = ($renderer ?: $this->view->editControlRenderer());

        /**
         * Only render groups in a tab view if there is more than 1 group because
         * when there is only 1 group, that means only the default "ungrouped"
         * or "other" set is present.
         */
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

    /**
     * Open the form table, optionally supplying a method and action attribute.
     *
     * @param string $action
     * @param string $method
     * @return string
     */
    public function open($action = '', $method = 'POST')
    {
        return sprintf(
            '<form role="form" action="%s" method="%s">',
            $this->view->escapeHtmlAttr($action),
            $this->view->escapeHtmlAttr($method)
        );
    }

    /**
     * Close the form tag.
     *
     * @return string
     */
    public function close()
    {
        return '</form>';
    }

    /**
     * Render a GroupedFields object using a Bootstrap tab view.
     *
     * @param GroupedFields $groupedFields
     * @param InputFilter $inputFilter
     * @param Renderer $renderer
     * @return string
     */
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

    /**
     * Render the supplied set of ungrouped fields, using the supplied InputFilter
     * to get validation messages, etc.
     *
     * @param Fields $fields
     * @param InputFilter $inputFilter
     * @param Renderer $renderer
     * @return string
     */
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

    /**
     * This particular bit of the markup is separated out so that sub-classes could
     * easily apply classes to it, primarily to take advantage of Bootstrap's
     * responsive grids.  Note that the returned string should include a sprintf()
     * string placeholder ("%s") so that validation-related classes can be added as
     * well.
     *
     * @return string
     */
    public function renderFormGroupOpenTag()
    {
        return '<div class="form-group%s">';
    }

    /**
     * Render the label for the supplied field, included a "required" flag when
     * appropriate.
     *
     * @param Renderer $renderer
     * @param FieldInterface $field
     * @param Input $input
     * @return string
     */
    public function renderLabel(Renderer $renderer, FieldInterface $field, Input $input = null)
    {
        return sprintf(
            '<label class="control-label" for="%s">%s%s</label>',
            $this->view->escapeHtmlAttr($field->getHtmlId()),
            $renderer->getLabelRenderer()->render($field),
            ($input && !$input->allowEmpty() ? $this->renderRequiredFlag() : '')
        );
    }

    /**
     * Render a "required" flag using Boostrap's glyphicons.
     *
     * @return string
     */
    public function renderRequiredFlag()
    {
        return ' <small><span title="Required" class="glyphicon glyphicon-asterisk text-danger"></span></small>';
    }

    /**
     * Render the supplied validation messages using Bootstraps' help-block class.
     *
     * @return string
     */
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

    /**
     * Render a simple submit button at the footer of the form.
     *
     * @param string $title
     * @return string
     */
    public function renderSubmitButton($title = 'Save Changes')
    {
        return sprintf(
            '<div class="form-group">
                <input type="submit" value="%s" class="btn btn-primary" />
            </div>',
            $this->view->escapeHtmlAttr($title)
        );
    }

    /**
     * When a field's rendered control contains its own label and is not a list,
     * we skip the rendering the label in this header.  This is meant to skip
     * redundant labels on checkbox controls while still rendering labels for
     * checkbox or radio button lists.
     *
     * @param string $output
     * @return boolean
     */
    protected function controlRequiresLabel($output)
    {
        return false === stripos($output, '<label') || false !== stripos($output, '<ul');
    }
}
