<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\FieldProvider;

use Dewdrop\Db\Eav\Field;
use Dewdrop\Db\Select;
use Dewdrop\Db\Table;

/**
 * A provider for EAV fields.  Enables a table object to see if a
 * given EAV attribute exists and instantiate a field object for it.
 */
class Eav implements ProviderInterface
{
    /**
     * The table containing this provider.
     *
     * @var \Dewdrop\Db\Table
     */
    private $table;

    /**
     * Create reference to supplied table.
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * Check to see if the table has an EAV definition and, if so, if it has
     * an attribute with the supplied name.
     *
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        return $this->table->hasEav() && $this->table->getEav()->hasAttribute($name);
    }

    /**
     * Create a field object for the EAV attribute with the supplied name.  You
     * should check to ensure the attribute exists prior to calling this method.
     *
     * @param string $name
     * @return \Dewdrop\Db\Eav\Field
     */
    public function instantiate($name)
    {
        $definition = $this->table->getEav();
        $metadata   = $definition->getFieldMetadata($name);
        $attribute  = $definition->getAttribute($name);

        $field = new Field($this->table, $name, $metadata);

        $field->setDefinition($definition);

        if ($attribute[$definition->getRequiredIndex()]) {
            $field->setRequired(true);
        }

        return $field;
    }

    /**
     * Get a list of the field names offered by this EAV definition.
     *
     * @return string
     */
    public function getAllNames()
    {
        if (!$this->table->hasEav()) {
            return array();
        }

        return array_keys($this->table->getEav()->getAttributes());
    }

    /**
     * Add the EAV values for all attributes to the provided Select.
     *
     * @param Select $select
     * @return Select
     */
    public function augmentSelect(Select $select)
    {
        if ($this->table->hasEav()) {
            $this->table->getEav()->augmentSelect($select);
        }

        return $select;
    }
}
