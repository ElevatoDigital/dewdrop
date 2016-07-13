<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\EditControl\Control as EditControl;
use Dewdrop\View\View;

/**
 * OptionInputDecorator leverages the CRUD APIs in the Dewdrop admin to allow
 * a user to add options to selects or checkbox lists.  It wraps an existing
 * option input and adds an "or add another option" link that reveals a form
 * based upon the editable fields from another CrudInterface implementing admin
 * component.
 */
class OptionInputDecorator extends AbstractHelper
{
    /**
     * Render the OptionInputDecorator.  The Field object is the fields whose
     * input we're wrapping.  The $controlComponent is the CrudInterface component
     * that created that Field.  The $optionComponent is the CrudInterface
     * component that manages the options available for $field.  And $controlHtml
     * is the original HTML that was generate for $field's input.
     *
     * @param FieldInterface $field
     * @param CrudInterface $controlComponent
     * @param CrudInterface $optionComponent
     * @param $controlHtml
     * @return string
     */
    public function direct(
        FieldInterface $field,
        CrudInterface $controlComponent,
        CrudInterface $optionComponent,
        $controlHtml
    ) {
        $this->view->headLink()->appendStylesheet($this->view->bowerUrl('/dewdrop/www/css/option-input-decorator.css'));
        $this->view->headScript()->appendFile($this->view->bowerUrl('/dewdrop/www/js/option-input-decorator.js'));

        return $this->partial(
            'option-input-decorator.phtml',
            [
                'field'               => $field,
                'originalControlHtml' => $controlHtml,
                'controlComponent'    => $controlComponent,
                'optionComponent'     => $optionComponent
            ]
        );
    }

    /**
     * A shortcut/utility method that sets up an EditControl.Control callback
     * to use OptionInputDecorator.
     *
     * @param FieldInterface $field
     * @param CrudInterface $controlComponent
     * @param CrudInterface $optionComponent
     * @param callable $originalHtmlCallback
     */
    public static function assignEditControlCallback(
        FieldInterface $field,
        CrudInterface $controlComponent,
        CrudInterface $optionComponent,
        callable $originalHtmlCallback = null
    ) {
        $field->assignHelperCallback(
            'EditControl.Control',
            OptionInputDecorator::createEditControlCallback(
                $field,
                $controlComponent,
                $optionComponent,
                $originalHtmlCallback
            )
        );
    }

    /**
     * Another shortcut/utility method that sets up an EditControl.Control callback
     * to use OptionInputDecorator.
     *
     * @param FieldInterface $field
     * @param CrudInterface $controlComponent
     * @param CrudInterface $optionComponent
     * @param callable $originalHtmlCallback
     */
    public static function createEditControlCallback(
        FieldInterface $field,
        CrudInterface $controlComponent,
        CrudInterface $optionComponent,
        callable $originalHtmlCallback = null
    ) {
        return function (
            EditControl $helper,
            View $view,
            $fieldPosition
        ) use (
            $field,
            $controlComponent,
            $optionComponent,
            $originalHtmlCallback
        ) {

            if (null === $originalHtmlCallback) {
                $originalHtmlCallback = $helper->detectCallableForField($field);
            }

            return $helper->getView()->optionInputDecorator(
                $field,
                $controlComponent,
                $optionComponent,
                $originalHtmlCallback($helper, $view, $fieldPosition)
            );
        };
    }
}
