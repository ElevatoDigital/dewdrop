<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Fields;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\GroupedFields;
use Dewdrop\Fields\GroupedFields\Group;
use Dewdrop\Fields\Helper\EditControl as Renderer;
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
        return $this->delegateIfArgsProvided(func_get_args());
    }

    /**
     * If the direct method was called with arguments, it delegates to this
     * method.  When a user supplies a \Dewdrop\Fields object, a
     * \Zend\InputFilter\InputFilter object and an optional TableCell field
     * helper as a renderer, the view helper can render the entire form in
     * a single step.
     *
     * @param Fields $fields
     * @param InputFilter $inputFilter
     * @param Renderer $renderer
     * @return string
     */
    public function directWithArgs(Fields $fields, InputFilter $inputFilter, Renderer $renderer = null)
    {
        $renderer = ($renderer ?: $this->view->editControlRenderer());

        return $this->open()
            . $this->renderContent($fields, $inputFilter, $renderer)
            . $this->renderSubmitButton()
            . $this->close();
    }

    /**
     * Open the form table, optionally supplying a method and action attribute.
     *
     * @param string $action
     * @param string $method
     * @param string $id
     * @param string $class
     * @return string
     */
    public function open($action = '', $method = 'POST', $id = '', $class = '')
    {
        return sprintf(
            '<form role="form" action="%s" method="%s" id="%s" class="%s">',
            $this->view->escapeHtmlAttr($action),
            $this->view->escapeHtmlAttr($method),
            $this->view->escapeHtmlAttr($id),
            $this->view->escapeHtmlAttr($class)
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
     * Only render groups in a tab view if there is more than 1 group because
     * when there is only 1 group, that means only the default "ungrouped"
     * or "other" set is present.
     *
     * @param Fields $fields
     * @param InputFilter $inputFilter
     * @param Renderer $renderer
     * @return mixed
     */
    public function renderContent(Fields $fields, InputFilter $inputFilter, Renderer $renderer)
    {
        if ($fields instanceof GroupedFields && 1 < count($fields->getGroups())) {
            $renderMethod = 'renderGroupedFields';
        } else {
            $renderMethod = 'renderFields';
        }

        return $this->$renderMethod($fields, $inputFilter, $renderer);
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

        /* @var $group Group */
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

        $fieldPosition = 0;

        foreach ($groupedFields->getGroups() as $index => $group) {
            if (count($group)) {
                $output .= sprintf(
                    '<div class="tab-pane-edit tab-pane fade%s" id="group_%d">',
                    (0 === $index ? ' in active' : ''),
                    $index
                );

                $output .= $this->renderFields($group, $inputFilter, $renderer, $fieldPosition);

                $output .= '</div>';

                $fieldPosition += count($group);
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
     * @param integer $fieldPosition
     * @return string
     */
    public function renderFields(Fields $fields, InputFilter $inputFilter, Renderer $renderer, $fieldPosition = 0)
    {
        $output = '';

        foreach ($fields->getEditableFields() as $field) {
            $output .= '<div class="">';
            $output .= $this->renderFieldContent($field, $inputFilter, $renderer, $fieldPosition);
            $output .= '</div>';

            $fieldPosition += 1;
        }

        return $output;
    }

    public function renderFieldsInTableRow(Fields $fields, InputFilter $inputFilter, Renderer $renderer)
    {
        $output = '<tr>';

        foreach ($fields->getEditableFields() as $field) {
            $output .= '<td>';
            $output .= $this->renderFieldContent($field, $inputFilter, $renderer, 100, false);
            $output .= '</td>';
        }

        $output .= '</tr>';

        return $output;
    }

    public function renderFieldContent(
        FieldInterface $field,
        InputFilter $inputFilter,
        Renderer $renderer,
        $position,
        $renderLabels = true
    ) {
        $output   = '';
        $input    = ($inputFilter->has($field->getId()) ? $inputFilter->get($field->getId()) : null);
        $messages = ($input ? $input->getMessages() : null);

        $output .= sprintf(
            $this->renderFormGroupOpenTag(),
            ($messages ? ' has-feedback has-error alert alert-danger' : '')
        );

        $controlOutput = $renderer->getControlRenderer()->render($field, $position);

        if ($renderLabels && $this->controlRequiresLabel($controlOutput)) {
            $output .= $this->renderLabel($field, $renderer, $input);
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
     * @param FieldInterface $field
     * @param Renderer $renderer
     * @param Input $input
     * @return string
     */
    public function renderLabel(FieldInterface $field, Renderer $renderer, Input $input = null)
    {
        $content = $renderer->getLabelRenderer()->render($field);

        if (!$content) {
            return '';
        }

        return sprintf(
            '<label class="control-label" for="%s">%s</label>',
            $this->view->escapeHtmlAttr($field->getHtmlId()),
            $this->renderLabelContent($field, $renderer, $input)
        );
    }

    /**
     * Render the content of a label for the supplied field, included a "required" flag when
     * appropriate.
     *
     * @param FieldInterface $field
     * @param Renderer $renderer
     * @param Input $input
     * @return string
     */
    public function renderLabelContent(FieldInterface $field, Renderer $renderer, Input $input = null)
    {
        return sprintf(
            '%s%s',
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
     * @param array $messages
     * @return string
     */
    public function renderMessages(array $messages)
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
     * Render a simple submit button at the footer of the form, with optional classes on that submit button. If the
     * $disableOnSubmit argument is false, the button will not be disabled upon form submission.
     *
     * @param string $title
     * @param string $classes optional
     * @param bool $disableOnSubmit
     * @return string
     */
    public function renderSubmitButton($title = 'Save Changes', $classes = '', $disableOnSubmit = true)
    {
        return sprintf(
            '<div class="form-group">
                <input type="submit" value="%s" class="%s btn btn-primary dewdrop-submit %s" />
            </div>',
            $this->view->escapeHtmlAttr($title),
            $this->view->escapeHtmlAttr($classes),
            $disableOnSubmit ? 'disable-on-submit' : ''
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
        return false === stripos($output, '<label')
            || false !== stripos($output, '<ul')
            || false !== stripos($output, 'option-input-decorator');
    }
}

