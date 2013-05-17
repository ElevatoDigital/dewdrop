<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Eav;

use Dewdrop\Db\Field as BaseField;

/**
 * An extension of the base field class for EAV fields.
 */
class Field extends BaseField
{
    /**
     * A reference to the EAV definition.  Can be used to automatically
     * generate validators, etc. for this field.
     *
     * @var \Dewdrop\Db\Eav\Definition
     */
    private $definition;

    /**
     * Set the EAV definition that contains the attribute represented by
     * this field object.
     *
     * @param Definition $definition
     * @return \Dewdrop\Db\Eav\Field
     */
    public function setDefinition(Definition $definition)
    {
        $this->definition = $definition;

        $attribute = $this->definition->getAttribute($this->name);

        $this
            ->setLabel($attribute['label'])
            ->setNote($attribute['note']);

        return $this;
    }
}
