<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\RowCollectionEditor;

use Dewdrop\Fields\RowCollectionEditor;
use Zend\InputFilter\Input;
use Zend\Validator\Callback;

/**
 * A factory to generate a \Zend\InputFilter\Input object to validate your
 * RowCollectionEditor prior to saving.
 */
class InputFilterFactory
{
    /**
     * The RowCollectionEditor we're validating.
     *
     * @var RowCollectionEditor
     */
    private $rowCollectionEditor;

    /**
     * Supply the RowCollectionEditor object.
     *
     * @param RowCollectionEditor $rowCollectionEditor
     */
    public function __construct(RowCollectionEditor $rowCollectionEditor)
    {
        $this->rowCollectionEditor = $rowCollectionEditor;
    }

    /**
     * Create the Input instance.
     *
     * We specify that validation should continue if the input is empty because
     * the RowCollectionEditor itself won't every receive a value.  It is just
     * iterating over its own editors and setting values/validating them each.
     *
     * @return Input
     */
    public function createInstance()
    {
        $input = new Input($this->rowCollectionEditor->getId());

        $input
            ->setRequired(false)
            ->setAllowEmpty(true)
            ->setContinueIfEmpty(true)
            ->getValidatorChain()
                ->attach($this->createCallbackValidator());

        return $input;
    }

    /**
     * Create the Callback validator that will be used to ensure the
     * RowCollectionEditor is valid.  We iterate over all the editors it has,
     * pass data to the editor via isValid() and return an error message if any
     * editor fails.
     *
     * @return Callback
     */
    protected function createCallbackValidator()
    {
        $validator = new Callback();

        $validator->setCallback(
            function () {
                return $this->rowCollectionEditor->isValid();
            }
        );

        $validator->setMessage(
            "Some errors were found in your {$this->rowCollectionEditor->getPluralTitle()}.
            Please double-check and try again.",
            Callback::INVALID_VALUE
        );

        return $validator;
    }
}
