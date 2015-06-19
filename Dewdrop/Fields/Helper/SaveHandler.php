<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Exception;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\SaveHandlerInterface;

/**
 * This helper will take fields returning an instance of SaveHandlerInterface
 * and call the save() method on that returned object.  Allows you to include
 * additional save logic on a field that will be triggered after the core save
 * logic in RowEditor.
 */
class SaveHandler extends HelperAbstract
{
    protected $name = 'savehandler';

    /**
     * Allow fields to perform any saving functionality they need.
     *
     * @param FieldInterface $field
     * @return boolean
     * @throws \Dewdrop\Fields\Exception\HelperCallableNotAvailableForField
     */
    public function save(FieldInterface $field)
    {
        $callable    = $this->getFieldAssignment($field);
        $saveHandler = call_user_func($callable);

        $saved = false;

        if (false !== $saveHandler) {
            if ($saveHandler instanceof SaveHandlerInterface) {
                $saveHandler->save();
                $saved = true;
            } else {
                throw new Exception(
                    "SaveHandler helper callback for field '{$field->getId()}' must return an instance of "
                    . 'SaveHandlerInterface.'
                );
            }
        }

        return $saved;
    }

    /**
     * In the case of SaveHandler, we assume a no-op.
     *
     * @param FieldInterface $field
     * @return bool|callable|mixed
     */
    public function detectCallableForField(FieldInterface $field)
    {
        return function () {
            return false;
        };
    }
}
