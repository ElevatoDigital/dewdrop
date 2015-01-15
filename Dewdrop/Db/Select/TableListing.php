<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Select;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Db\Expr;
use Dewdrop\Db\FieldProvider\ProviderInterface;
use Dewdrop\Db\Select;
use Dewdrop\Db\Table;
use Dewdrop\Exception;

/**
 * This class can assist in creating a Select object to provide the data for
 * Listing.  It traverses foreign keys, looks at many-to-many relationships and
 * EAV fields, etc. and folds them into the Select to ensure that they conform
 * to naming conventions Dewdrop uses when rendering Listings.
 *
 * There are a couple known limitations with this tool right now:
 *
 * @todo Allow retrieval of more fields from referenced tables.
 *
 * If, for example, you wanted to show all the fields in an address rather than just
 * a single value to represent that foreign key value.
 */
class TableListing
{
    /**
     * The table for which we're generating the listing.
     *
     * @var Table
     */
    private $table;

    /**
     * The DB adapter associated with the table.
     *
     * @var DbAdapter
     */
    private $db;

    /**
     * The Select object that was generated for the listing.  If you've
     * manipulated the options or need to regenerate the listing for some
     * other reason, call reset() and then select().
     *
     * @var Select
     */
    private $select;

    /**
     * Table aliases used so far while building the Select.
     *
     * @var array
     */
    private $aliases = [];

    /**
     * Any custom reference table title columns.  We'll attempt use "name",
     * "title" or whatever the first column in the referenced table happens
     * to be, but you can override that behavior with setReferenceTitleColumn().
     *
     * @param array
     */
    private $referenceTitleColumns = [];

    /**
     * Provide the table for which we're generated a listing Select.
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->db    = $this->table->getAdapter();
    }

    /**
     * Override the reference title column for a foreign key.  The first
     * parameter should be the name of the foreign key column in the primary
     * table.  The second parameter is a column name from the referenced table,
     * an Expr object, or a callback that will be passed the generated table
     * alias for the referenced table for use in creating an Expr.
     *
     * @param string $foreignKeyColumn
     * @param mixed $titleColumn
     * @return $this
     */
    public function setReferenceTitleColumn($foreignKeyColumn, $titleColumn)
    {
        $this->referenceTitleColumns[$foreignKeyColumn] = $titleColumn;

        return $this;
    }

    /**
     * Reset the generated Select and associated table aliases, if you've
     * tweaked options and need to generate the Select again.
     *
     * @return $this
     */
    public function reset()
    {
        $this->select  = null;
        $this->aliases = [];

        return $this;
    }

    /**
     * Get the generated Select object.  Will generated it, if that hasn't
     * occurred yet.
     *
     * @return Select
     */
    public function select()
    {
        if (!$this->select) {
            $this->select = $this->generateSelect();
        }

        return $this->select;
    }

    /**
     * Generate the Select object for this listing.  Will include the columns
     * from the listing's Table, any foreign key references, and also allow all
     * field providers to augment the Select as needed.
     *
     * @return Select
     */
    private function generateSelect()
    {
        $select = $this->table->select();

        $this->selectFromTable($select);
        $this->selectForeignKeyValues($select);
        $this->selectFieldProviderValues($select);

        return $select;
    }

    /**
     * Get the basic columns contained in the listing table.
     *
     * @param Select $select
     * @return Select
     */
    private function selectFromTable(Select $select)
    {
        return $select->from(
            [$this->getAlias($this->table->getTableName()) => $this->table->getTableName()],
            ['*']
        );
    }

    /**
     * Join against any tables referenced by foreign keys in order to get a
     * reasonable value to display for them.  If the foreign key in the listing
     * table is nullable, we'll use a LEFT JOIN so that a missing foreign key
     * value does not exclude the record from the result set.
     *
     * We follow a naming convention for these values in result sets: the
     * foreign key ends with "_id" and the value in the result set is aliased to
     * that foreign key column name minus "_id".  For example, let's say the
     * foreign key column was "favorite_book_id".  In the result set for the
     * listing query, we'd include an alias of "favorite_book" that pointed
     * to the "title" column of the referenced "books" table.  That way, both
     * the integer favorite_book_id and the title favorite_book are available
     * when we render the listing.
     *
     * @param Select $select
     * @return Select
     */
    private function selectForeignKeyValues(Select $select)
    {
        $tableAlias     = $this->getAlias($this->table->getTableName());
        $tableInstances = [];

        foreach ($this->table->getMetadata('references') as $column => $reference) {
            $metadata = $this->table->getMetadata('columns', $column);

            if ($metadata['NULLABLE']) {
                $join = 'joinLeft';
            } else {
                $join = 'join';
            }

            $refTable = $reference['table'];

            if (!array_key_exists($refTable, $tableInstances)) {
                $tableInstances[$refTable] = 0;
            }

            $refAlias = $this->getAlias($refTable, $tableInstances[$refTable], $column);

            $tableInstances[$refTable] += 1;

            $columnAlias = preg_replace('/_id$/i', '', $column);
            $titleColumn = $this->findReferenceTitleColumn($column, $reference, $refAlias);

            $select->$join(
                [$refAlias => $refTable],
                sprintf(
                    '%s = %s',
                    $this->db->quoteIdentifier("{$tableAlias}.{$column}"),
                    $this->db->quoteIdentifier("{$refAlias}.{$reference['column']}")
                ),
                [$columnAlias => $titleColumn]
            );
        }

        return $select;
    }

    /**
     * Allow each field provider on the Table to augment the Select object.
     * This is how many-to-many and EAV fields get their values folded into a
     * listing query.
     *
     * @param Select $select
     * @return Select
     */
    private function selectFieldProviderValues(Select $select)
    {
        /* @var $provider ProviderInterface */
        foreach ($this->table->getFieldProviders() as $provider) {
            $provider->augmentSelect($select);
        }

        return $select;
    }

    /**
     * Get an alias for the provided table name.
     *
     * @param string $tableName
     * @param null|integer $instance
     * @return string
     */
    private function getAlias($tableName, $instance = null, $anchorColumn = null)
    {
        $aliasKey = $tableName;

        // Used to support multiple joins on the same table
        if (null !== $instance) {
            $aliasKey .= $instance;
        }

        if (array_key_exists($aliasKey, $this->aliases)) {
            return $this->aliases[$aliasKey];
        }

        $chars = 0;
        $alias = null;

        while ((!$alias || in_array($alias, $this->aliases)) && $chars < strlen($tableName)) {
            $chars += 1;
            $alias  = substr($tableName, 0, $chars);
        }

        if (in_array($alias, $this->aliases)) {
            $alias .= $instance;
        }

        $this->aliases[$aliasKey] = $alias;

        return $alias;
    }

    /**
     * Determine a reasonable value to use as a title for a foreign key
     * reference.  We'll look for a name or title in the referenced table.  If
     * neither is present, we just grab the first column, which is probably the
     * ID itself.
     *
     * However, you can call setReferenceTitleColumn() to supply a different
     * column to use or an Expr object.
     *
     * @param $localColumn
     * @param array $reference
     * @param string $alias
     * @return string|Expr
     */
    private function findReferenceTitleColumn($localColumn, array $reference, $alias)
    {
        if (array_key_exists($localColumn, $this->referenceTitleColumns)) {
            $value = $this->referenceTitleColumns[$localColumn];

            if (is_callable($value)) {
                $value = call_user_func($value, $alias);

                if (!is_string($value) && !$value instanceof Expr) {
                    throw new Exception('Title column callbacks should return a string or Expr.');
                }
            }

            return $value;
        }

        $metadata = $this->db->getTableMetadata($reference['table']);
        $columns  = array_keys($metadata['columns']);

        if (in_array('name', $columns)) {
            return 'name';
        } elseif (in_array('title', $columns)) {
            return 'title';
        } else {
            return array_shift($columns);
        }
    }
}
