<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\FieldProvider;

use Dewdrop\Db\Field;
use Dewdrop\Db\Select;
use Dewdrop\Db\Table;

/**
 * This field provider allows checking for and instantiating fields
 * that are associated with concrete, physical columns in the underlying
 * database table.
 */
class Metadata implements ProviderInterface
{
    /**
     * The table object containing this provider.
     *
     * @var \Dewdrop\Db\Table
     */
    private $table;

    /**
     * Create a reference to this provider's table.
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * Check to see if a column exists in the DB table matching the supplied
     * name.
     *
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        return false !== $this->table->getMetadata('columns', $name);
    }

    /**
     * Create a field object for this column matching the supplied name.
     *
     * @param string $name
     * @return \Dewdrop\Db\Field
     */
    public function instantiate($name)
    {
        return new Field(
            $this->table,
            $name,
            $this->table->getMetadata('columns', $name)
        );
    }

    /**
     * Get a list of the field names available from this provider.
     *
     * @return array
     */
    public function getAllNames()
    {
        return array_keys($this->table->getMetadata('columns'));
    }

    /**
     * This is a no-op for the metadata provider.
     *
     * @param Select $select
     * @return Select
     */
    public function augmentSelect(Select $select)
    {
        return $select;
    }
}
