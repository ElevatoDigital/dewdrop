<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\ManyToMany;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Db\Expr;
use Dewdrop\Db\Row;
use Dewdrop\Db\Select;
use Dewdrop\Db\Table;
use Dewdrop\Exception;

/**
 * This class configures and offers utilities for managing many-to-many
 * relationships in your database.  Typically, you won't instantiate and
 * interact with this class directly.  Rather, you'll register a new
 * relationship by calling hasMany() in your table class.
 *
 * Typically, this class only needs a reference to the \Dewdrop\Db\Table
 * object that created it and the name of the cross-reference table
 * leveraged for this many-to-many relationship.  From there it will
 * determine which column references back from the cross-reference table
 * to the source table and which other foreign key in the cross-reference
 * table points to the options table.
 *
 * If those items can't be determined, you can pass those additional options
 * to this class explicitly (typically using the $additionalOptions parameter
 * of \Dewdrop\Db\Table).
 */
class Relationship
{
    /**
     * A reference to the Dewdrop\Db\Table object that created this relationship
     * in a hasMany() call.
     *
     * @var \Dewdrop\Db\Table
     */
    private $sourceTable;

    /**
     * The name of the column in the source table that is referenced by the
     * cross-reference table.  This is typically, though not necessarily, the
     * source table's primary key.
     *
     * @var string
     */
    private $sourceColumnName;

    /**
     * The name of the cross-reference table where this relationship is stored
     * in the database.
     *
     * @var string
     */
    private $xrefTableName;

    /**
     * The name of the column in the cross-reference table that points back to
     * the source table.
     *
     * @var string
     */
    private $xrefAnchorColumnName;

    /**
     * The name of the column in the cross-reference table that points to the
     * reference, or options, table.
     *
     * @var string
     */
    private $xrefReferenceColumnName;

    /**
     * The name of the reference, or options, table that sits on the other side
     * of the many-to-many relationship from the source table.
     *
     * @var string
     */
    private $referenceTableName;

    /**
     * The name of the reference column that ties the cross-reference table to
     * the reference table.
     *
     * @var string
     */
    private $referenceColumnName;

    /**
     * The loaded DB metadata definition for the cross reference table.
     *
     * @var array
     */
    private $xrefMetadata;

    /**
     * Title column names or Expr objects that can be used to represent cross-reference
     * values.  If the reference table has a "name" or "title" column that will be used
     * automatically, but you can use setReferenceTitleColumn() to override that behavior.
     *
     * @var string|Expr
     */
    private $referenceTitleColumn;

    /**
     * Create relationship using the supplied source table and cross-reference
     * table name.
     *
     * @param Table $sourceTable
     * @param string $xrefTableName
     */
    public function __construct(Table $sourceTable, $xrefTableName)
    {
        $this
            ->setSourceTable($sourceTable)
            ->setXrefTableName($xrefTableName);
    }

    /**
     * Set one or more options using the supplied array of name-value
     * pairs.  If the named option does not have a setter method in this
     * class, an exception will be thrown.
     *
     * @throws \Dewdrop\Exception
     * @param array $options
     * @return \Dewdrop\Db\ManyToMany\Relationship
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $setter = 'set' . ucfirst($name);

            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } else {
                throw new Exception("ManyToMany\\Relationship: Unknown option \"$name\"");
            }
        }

        return $this;
    }

    /**
     * Set the table this relationship was created by and is managed by.
     *
     * @param Table $sourceTable
     * @return \Dewdrop\Db\ManyToMany\Relationship
     */
    public function setSourceTable(Table $sourceTable)
    {
        $this->sourceTable = $sourceTable;

        return $this;
    }

    /**
     * Get the source table this relationship is associated with.
     *
     * @return Table
     */
    public function getSourceTable()
    {
        return $this->sourceTable;
    }

    /**
     * Set the name of the cross-reference table that is used to store the
     * records for this many-to-many relationship.
     *
     * @param string $xrefTableName
     * @return \Dewdrop\Db\ManyToMany\Relationship
     */
    public function setXrefTableName($xrefTableName)
    {
        $this->xrefTableName = $xrefTableName;

        return $this;
    }

    /**
     * Set the name of the column in the source table that is referenced by
     * the cross reference table.
     *
     * @param string $sourceColumnName
     * @return \Dewdrop\Db\ManyToMany\Relationship
     */
    public function setSourceColumnName($sourceColumnName)
    {
        $this->sourceColumnName = $sourceColumnName;

        return $this;
    }

    /**
     * Get the name of the column in the source table that is refernced by
     * the cross-reference table.  If this is not set explicitly, we'll
     * attempt to auto-detect it using the cross-reference table's metadata,
     * looking for any references that point back to the source table.
     *
     * @throws \Dewdrop\Exception
     * @return string
     */
    public function getSourceColumnName()
    {
        if (!$this->sourceColumnName) {
            $metadata = $this->loadXrefTableMetadata();

            foreach ($metadata['references'] as $column => $reference) {
                if ($reference['table'] === $this->sourceTable->getTableName()) {
                    $this->sourceColumnName = $reference['column'];
                    break;
                }
            }

            if (!$this->sourceColumnName) {
                throw new Exception(
                    'ManyToMany\Relationship: Could not find many-to-many relationship '
                    . 'source column referenced by the cross-reference table.  Please '
                    . 'specify it manually in your hasMany() call.'
                );
            }
        }

        return $this->sourceColumnName;
    }

    /**
     * Set the name of the column in the cross-reference table that is used
     * to tie it back to the source table.
     *
     * @param string $xrefAnchorColumnName
     * @return \Dewdrop\Db\ManyToMany\Relationship
     */
    public function setXrefAnchorColumnName($xrefAnchorColumnName)
    {
        $this->xrefAnchorColumnName = $xrefAnchorColumnName;

        return $this;
    }

    /**
     * Get the name of the column that ties the cross-reference table back to
     * the source table that created this many-to-many relationship.  If this
     * is not set manually, we'll look at the references defined on the cross-
     * reference table and return the column from the first references that
     * points back to the source table.
     *
     * @throws \Dewdrop\Exception
     * @return string
     */
    public function getXrefAnchorColumnName()
    {
        if (!$this->xrefAnchorColumnName) {
            $metadata = $this->loadXrefTableMetadata();

            foreach ($metadata['references'] as $column => $reference) {
                if ($reference['table'] === $this->sourceTable->getTableName()) {
                    $this->xrefAnchorColumnName = $column;
                    break;
                }
            }

            if (!$this->xrefAnchorColumnName) {
                throw new Exception(
                    'ManyToMany\Relationship: Could not find many-to-many relationship '
                    . 'anchor column that references back to the source table.  Please '
                    . 'specify it manually in your hasMany() call.'
                );
            }
        }

        return $this->xrefAnchorColumnName;
    }

    /**
     * Set the name of the column in the cross-reference table that is used
     * to reference the table containing the options for this relationship.
     * For example, if you've added this relationship to your "projects"
     * table and the cross-reference table is "project_staff", this column
     * is likely "staff_id".
     *
     * @param string $xrefReferenceColumnName
     * @return \Dewdrop\Db\ManyToMany\Relationship
     */
    public function setXrefReferenceColumnName($xrefReferenceColumnName)
    {
        $this->xrefReferenceColumnName = $xrefReferenceColumnName;

        return $this;
    }

    /**
     * Get the cross-reference table reference column.  If it has not been set
     * already, we try to auto-detect the column name using the cross-reference
     * table's metadata.  Basically, we look for a foreign key that does not
     * reference the source table.
     *
     * @return string
     */
    public function getXrefReferenceColumnName()
    {
        if (!$this->xrefReferenceColumnName) {
            $metadata = $this->loadXrefTableMetadata();

            foreach ($metadata['references'] as $column => $reference) {
                if ($column !== $this->getXrefAnchorColumnName()) {
                    $this->xrefReferenceColumnName = $column;
                    break;
                }
            }

            if (!$this->xrefReferenceColumnName) {
                throw new Exception(
                    'ManyToMany\Relationship: Could not determine reference '
                    . "column name for {$this->xrefTableName} table.  Please "
                    . 'specify it manually in your hasMany() call.'
                );
            }
        }

        return $this->xrefReferenceColumnName;
    }

    /**
     * Set the name of the table where the options are stored for this
     * relationship.  For example, if you've added this relationship to your
     * "projects" table and the cross-reference table is "project_staff", this
     * property will likely be "staff".
     *
     * @param string $referenceTableName
     * @return \Dewdrop\Db\ManyToMany\Relationship
     */
    public function setReferenceTableName($referenceTableName)
    {
        $this->referenceTableName = $referenceTableName;

        return $this;
    }

    /**
     * Get the reference table name.  If it hasn't been set already, we will
     * retrieve the table name using the cross-reference table metadata.
     *
     * @return string
     */
    public function getReferenceTableName()
    {
        if (!$this->referenceTableName) {
            $meta = $this->loadXrefTableMetadata();

            $this->referenceTableName = $meta['references'][$this->getXrefReferenceColumnName()]['table'];
        }

        return $this->referenceTableName;
    }

    /**
     * Set the name of the column in the reference table that is linked to
     * from the cross-reference table.  For example, if you've added this
     * relationship to your "projects" table and the cross-reference table is
     * "project_staff", this property will like be "staff_id" or "id", depending
     * upon your primary key naming conventions.
     *
     * @param string $referenceColumnName
     * @return \Dewdrop\Db\ManyToMany\Relationship
     */
    public function setReferenceColumnName($referenceColumnName)
    {
        $this->referenceColumnName = $referenceColumnName;

        return $this;
    }

    /**
     * Get the reference column name.  If it hasn't been set already, we will
     * retrieve the column name using the cross-reference table metadata.
     *
     * @return string
     */
    public function getReferenceColumnName()
    {
        if (!$this->referenceColumnName) {
            $meta = $this->loadXrefTableMetadata();

            $this->referenceColumnName = $meta['references'][$this->getXrefReferenceColumnName()]['column'];
        }

        return $this->referenceColumnName;
    }

    /**
     * Set the name of the column (or an Expr) that should be used when building
     * the list of values to add to a Select in augmentSelect().
     *
     * @param string|Expr $titleColumn
     * @return $this
     */
    public function setReferenceTitleColumn($titleColumn)
    {
        $this->referenceTitleColumn = $titleColumn;

        return $this;
    }

    /**
     * Augment the provided Select object with a comma-separated list of values for this
     * many-to-many relationship, using the name parameter as the name of the value in
     * the resultset.
     *
     * @param Select $select
     * @param string $name
     * @return Select
     */
    public function augmentSelect(Select $select, $name)
    {
        $anchorColumn = $select->quoteWithAlias($this->sourceTable->getTableName(), $this->getSourceColumnName());
        $titleColumn  = $this->findReferenceTitleColumn();
        $driver       = $select->getAdapter()->getDriver();

        if ($driver instanceof \Dewdrop\Db\Driver\Pdo\Pgsql) {
            $expr = new Expr(
                "ARRAY_TO_STRING(
                    ARRAY(
                        SELECT {$titleColumn}
                        FROM {$this->getReferenceTableName()} ref
                        JOIN {$this->xrefTableName} xref
                            ON xref.{$this->xrefReferenceColumnName} = ref.{$this->getReferenceColumnName()}
                        WHERE xref.{$this->xrefAnchorColumnName} = {$anchorColumn}
                        ORDER BY {$titleColumn}
                    ),
                    ', '
                )"
            );
        } else {
            $expr = new Expr(
                "(SELECT
                    GROUP_CONCAT({$titleColumn} SEPARATOR ', ')
                    FROM {$this->getReferenceTableName()} ref
                    JOIN {$this->xrefTableName} xref
                        ON xref.{$this->xrefReferenceColumnName} = ref.{$this->getReferenceColumnName()}
                    WHERE xref.{$this->xrefAnchorColumnName} = {$anchorColumn}
                    ORDER BY {$titleColumn}
                )"
            );
        }

        return $select->columns([$name => $expr]);
    }

    /**
     * Using the supplied source table row, retrieve the initial value for
     * this relationship.  This is typically called by \Dewdrop\Db\Row when
     * get() is first called for this relationship.
     *
     * @param Row $row
     * @return array
     */
    public function loadInitialValue(Row $row)
    {
        $value = $row->get($this->getSourceColumnName());

        // If the anchor column has no value, we can assume this relationship has no value
        if (!$value) {
            return array();
        }

        $db   = $row->getTable()->getAdapter();
        $stmt = $db->select();

        $whereName = $db->quoteIdentifier("{$this->xrefTableName}.{$this->getXrefAnchorColumnName()}");

        $stmt
            ->from(
                $this->xrefTableName,
                array($this->getXrefReferenceColumnName())
            )
            ->where(
                "$whereName = ?",
                $value
            );

        return $db->fetchCol($stmt);
    }

    /**
     * Get the metadata associated with the reference column in this relationship's
     * cross reference table.  This metadata is used in \Dewdrop\Db\ManyToMany\Field
     * objects to allow them validate array members according to the DB metadata.
     *
     * @return array
     */
    public function getFieldMetadata()
    {
        $meta = $this->loadXrefTableMetadata();

        return $meta['columns'][$this->getXrefReferenceColumnName()];
    }

    /**
     * Get the reference definition from the cross-reference table metadata that can
     * be used by to configure an OptionPairs object.  Whereas in a one-to-many
     * relationship, the reference needed to get a list of options would be on the
     * source table itself, in the case of a many-to-many relationship, that reference
     * is instead on the cross-reference table.
     *
     * @throws \Dewdrop\Exception
     * @return array
     */
    public function getOptionPairsReference()
    {
        $meta = $this->loadXrefTableMetadata();

        if (isset($meta['references'][$this->getXrefReferenceColumnName()])) {
            return $meta['references'][$this->getXrefReferenceColumnName()];
        }

        throw new Exception(
            'ManyToMany\Relationship: Could not find a reference for retrieving '
            . 'OptionPairs.'
        );
    }

    /**
     * Get a subquery that can be used when filtering by a many-to-many field.
     * Basically gets all the anchor column values that are associated with the
     * selected reference column value in the cross-reference table.
     *
     * @param integer $value
     * @return string
     */
    public function getFilterSubquery($value)
    {
        $db = $this->sourceTable->getAdapter();

        $sql = "SELECT {$db->quoteIdentifier($this->xrefTableName . '.' . $this->xrefAnchorColumnName)}
            FROM {$db->quoteIdentifier($this->xrefTableName)} ";

        if ($value) {
            $sql .= $db->quoteInto(
                "WHERE {$db->quoteIdentifier($this->xrefTableName . '.' . $this->xrefReferenceColumnName)} = ?",
                $value
            );
        }

        return $sql;
    }

    /**
     * Save new values for this relationship.  This is typically called from
     * \Dewdrop\Db\Table when running insert() or update().
     *
     * @param array $xrefValues
     * @param mixed $anchorValue
     * @return integer
     */
    public function save($xrefValues, $anchorValue)
    {
        if (!is_array($xrefValues)) {
            $xrefValues = array();
        }

        $db = $this->sourceTable->getAdapter();

        // Delete values that have been removed
        $db->delete(
            $this->xrefTableName,
            $this->buildDeleteWhereClause($db, $xrefValues, $anchorValue)
        );

        // Get existing values so we can avoid inserting duplicates
        $anchorName = $db->quoteIdentifier(
            "{$this->xrefTableName}.{$this->getXrefAnchorColumnName()}"
        );

        $existing = $db->fetchCol(
            $db->select()
                ->from($this->xrefTableName, array($this->getXrefReferenceColumnName()))
                ->where("{$anchorName} = ?", $anchorValue)
        );

        // Insert new values
        foreach ($xrefValues as $value) {
            if (!in_array($value, $existing)) {
                $db->insert(
                    $this->xrefTableName,
                    array(
                        $this->xrefAnchorColumnName    => $anchorValue,
                        $this->xrefReferenceColumnName => $value
                    )
                );
            }
        }

        return count($xrefValues);
    }

    /**
     * Load the cross-reference table metadata.
     *
     * @return array
     */
    protected function loadXrefTableMetadata()
    {
        if (!$this->xrefMetadata) {
            $this->xrefMetadata = $this->sourceTable->getAdapter()->getTableMetadata($this->xrefTableName);
        }

        return $this->xrefMetadata;
    }

    /**
     * Build a WHERE clause for use in deleting cross-reference values while
     * saving.  If we have no cross-reference values, we delete all cross-reference
     * rows with the given anchor value.  Otherwise, we only delete those rows
     * that are not related to the newly supplied $xrefValues.
     *
     * @param DbAdapter $db
     * @param array $xrefValues
     * @param mixed $anchorValue
     * @return string
     */
    private function buildDeleteWhereClause(DbAdapter $db, array $xrefValues, $anchorValue)
    {
        $anchorName = $db->quoteIdentifier(
            "{$this->xrefTableName}.{$this->getXrefAnchorColumnName()}"
        );

        if (!count($xrefValues)) {
            $where = "{$anchorName} = ?";
            $where = $db->quoteInto($where, $anchorValue);
        } else {
            $refName = $db->quoteIdentifier(
                "{$this->xrefTableName}.{$this->getXrefReferenceColumnName()}"
            );

            $where = "{$anchorName} = ? AND {$refName} NOT IN (?)";
            $where = $db->quoteInto($where, $anchorValue, null, 1);
            $where = $db->quoteInto($where, $xrefValues, null, 1);
        }

        return $where;
    }

    /**
     * Attempt to get a reasonable reference title column for many-to-many
     * values retrieved in augmentSelect().  Will use name or title, if available,
     * and then fall back to the first column in the reference table.  You can
     * override this behavior with setReferenceTitleColumn().
     *
     * @return Expr|string
     * @throws Exception
     */
    private function findReferenceTitleColumn()
    {
        if ($this->referenceTitleColumn) {
            return $this->referenceTitleColumn;
        }

        $metadata = $this->sourceTable->getAdapter()->getTableMetadata($this->getReferenceTableName());
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
