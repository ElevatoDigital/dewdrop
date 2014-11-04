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
     * The attribute information associated with this field in the EAV
     * definition.
     *
     * @var array
     */
    private $attribute;

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
        $this->attribute  = $this->definition->getAttribute($this->name);

        $this
            ->setLabel($this->attribute['label'])
            ->setNote($this->attribute['note']);

        return $this;
    }

    /**
     * Get the name of the edit view helper assigned to this field in the
     * EAV definition.
     *
     * @return string
     */
    public function getEditHelperName()
    {
        return $this->attribute['edit_helper'];
    }

    /**
     * Get the name of the display view helper assigned to this field in the
     * EAV definition.
     *
     * @return string
     */
    public function getDisplayHelperName()
    {
        return $this->attribute['display_helper'];
    }

    /**
     * Get the attribute definition for this field from the EAV definition.
     *
     * @return array
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}
