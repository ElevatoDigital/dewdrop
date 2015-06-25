<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\FieldProvider;

use Dewdrop\Db\ManyToMany\Field;
use Dewdrop\Db\ManyToMany\Relationship;
use Dewdrop\Db\Select;
use Dewdrop\Db\Table;

/**
 * A field provider that allows checking and instantiating field
 * objects for the ManyToMany relationships assigned to the provider's
 * table object.
 */
class ManyToMany implements ProviderInterface
{
    /**
     * The table object containing this provider.
     *
     * @var \Dewdrop\Db\Table
     */
    private $table;

    /**
     * Create a reference to the supplied table object.
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * Check to see if there is a many-to-many relationship with the supplied
     * name.
     *
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        return $this->table->hasManyToManyRelationship($name);
    }

    /**
     * Create a field object for the many-to-many relationship matching the
     * supplied name.
     *
     * @param string $name
     * @return \Dewdrop\Db\ManyToMany\Field
     */
    public function instantiate($name)
    {
        $rel   = $this->table->getManyToManyRelationship($name);
        $field = new Field($this->table, $name, $rel->getFieldMetadata());

        $field->setManyToManyRelationship($rel);

        return $field;
    }

    /**
     * Get a list of field names supported by many-to-many relationships
     * assigned to this provider's table object.
     *
     * @return array
     */
    public function getAllNames()
    {
        return array_keys($this->table->getManyToManyRelationships());
    }

    /**
     * Augment the provided Select by adding a comma-separated list of
     * many-to-many values for all relationships in this field provider.
     *
     * @param Select $select
     * @return Select
     */
    public function augmentSelect(Select $select)
    {
        /* @var $relationship Relationship */
        foreach ($this->table->getManyToManyRelationships() as $name => $relationship) {
            $relationship->augmentSelect($select, $name);
        }

        return $select;
    }
}
