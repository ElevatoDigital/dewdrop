<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Eav;

use Dewdrop\Db\Row;
use Dewdrop\Db\Select;
use Dewdrop\Db\Table;
use Dewdrop\Exception;

/**
 * This class handles an EAV definition attached to a \Dewdrop\Db\Table
 * object.  The EAV definition can load and save values for an EAV field,
 * retrieve the list of available attributes, optionally filtered (e.g.
 * to a single user account's attribute), etc.
 *
 * To use EAV, you should add an attribute table and a set of value tables
 * to your database.  For the following example, assume the table the EAV
 * is connected to in your database is called "widgets". By convention,
 * the attribute table follows this format:
 *
 * <pre>
 * widgets_attributes
 * </pre>
 *
 * The value tables should be named like this:
 *
 * <pre>
 * widgets_eav_values_varchar
 * widgets_eav_values_text
 * widgets_eav_values_datetime
 * widgets_eav_values_decimal
 * widgets_eav_values_int
 * widgets_eav_values_boolean
 * </pre>
 *
 * You can modify the "_eav_values_" portion of the value table names by
 * changing the $valueTablePrefix property.
 *
 * If you'd like to just stick with the Dewdrop naming conventions, you
 * can generate the EAV tables using the "gen-eav" CLI command.
 */
class Definition
{
    /**
     * The Table object this EAV definition is associated with.
     *
     * @var \Dewdrop\Db\Table
     */
    private $table;

    /**
     * The name of the table where EAV attributes are stored.  Defaults to the name
     * of the table that this EAV definition was registered to with a suffix of
     * "_attributes".
     *
     * @var string
     */
    private $attributeTableName;

    /**
     * The prefix that will be used for value table names.  The value table's full
     * name is composed of the name of the table that registered this EAV definition,
     * then this prefix, and then the backend type of the attribute that is being
     * saved.
     *
     * @var string
     */
    private $valueTablePrefix = '_eav_values_';

    /**
     * A callback that can be used to filter the attribute list for this EAV
     * definition.  If, for example, you have a SaaS application where each
     * account holder is able to create their own custom fields, you might want
     * to filter this attribute list like this:
     *
     * <pre>
     * $table->getEav()->setAttributeFilterCallback(
     *     function ($stmt) use ($currentUser) {
     *         $stmt->where(
     *             'account_id = ?',
     *             $currentUser->get('account_id')
     *         );
     *     }
     * );
     * </pre>
     *
     * @var mixed
     */
    private $attributeFilterCallback;

    /**
     * The available attributes for this EAV definition.  This is lazy-loaded
     * the first time you attempt to call any attribute related method.
     *
     * @var array
     */
    private $attributes;

    /**
     * The index in the attribute definition that determines whether the field
     * is required.
     *
     * @var string
     */
    private $requiredIndex = 'is_required';

    /**
     * The name of a column in the attributes DB table that will be true when
     * the attribute should be treated as if it was deleted.
     *
     * @var string
     */
    private $deletedIndex = 'deleted';

    /**
     * Register a new EAV definition to supplied table and set any additional
     * options specified in the supplied array.
     *
     * @param Table $table
     * @param array $options
     */
    public function __construct(Table $table, array $options = array())
    {
        $this->table = $table;

        $this->setOptions($options);
    }

    /**
     * Set multiple options using the supplied array of option name and value
     * pairs.
     *
     * @param array $options
     * @return \Dewdrop\Db\Eav\Definition
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $setter = 'set' . ucfirst($name);

            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } else {
                throw new Exception("Eav\Definition: Unknown option \"{$name}\"");
            }
        }
    }

    /**
     * Set the name of the table where you want to store attributes (not values,
     * but the actual rules/metadata for the attribute itself) for this EAV
     * definition.
     *
     * @param string $attributeTableName
     * @return \Dewdrop\Db\Eav\Definition
     */
    public function setAttributeTableName($attributeTableName)
    {
        $this->attributeTableName = $attributeTableName;

        return $this;
    }

    /**
     * Set the prefix that will come before the backend type in a value table's
     * name.
     *
     * @param string $valueTablePrefix
     * @return \Dewdrop\Db\Eav\Definition
     */
    public function setValueTablePrefix($valueTablePrefix)
    {
        $this->valueTablePrefix = $valueTablePrefix;

        return $this;
    }

    /**
     * Set a callback for filtering the initial \Dewdrop\Db\Select object that
     * retrieves the list of available attributes.  This can be any valid PHP
     * callable (i.e. function name as string, anonymous function, or method
     * array).  The function should return the modified \Dewdrop\Db\Select.
     *
     * @param mixed $attributeFilterCallback
     * @return \Dewdrop\Db\Eav\Definition
     */
    public function setAttributeFilterCallback($attributeFilterCallback)
    {
        $this->attributeFilterCallback = $attributeFilterCallback;

        return $this;
    }

    /**
     * Get the name of the table where attributes are stored.  If none is set,
     * we use the name of the table this EAV definition is registered to followed
     * by "_attributes".
     *
     * @return string
     */
    public function getAttributeTableName()
    {
        if (!$this->attributeTableName) {
            $this->attributeTableName = $this->table->getTableName() . '_eav_attributes';
        }

        return $this->attributeTableName;
    }

    /**
     * Check to if there is an attribute with the supplied name.
     *
     * @param string $name
     * @return boolean
     */
    public function hasAttribute($name)
    {
        $this->loadAttributes();

        return array_key_exists($name, $this->attributes);
    }

    /**
     * Set the index of the column in the attributes table that will be used to
     * determine if an attribute is required or not.  Useful if you require
     * an attribute in some contexts but not others (e.g. you have a column
     * called "is_required_public" and another called "is_required_admin" and
     * need to consult a different column depending upon who is logged in).
     *
     * @param string $requiredIndex
     * @return $this
     */
    public function setRequiredIndex($requiredIndex)
    {
        $this->requiredIndex = $requiredIndex;

        return $this;
    }

    /**
     * Get the index/name of the column in the attribute definition that tells us
     * if a value is required.
     *
     * @return string
     */
    public function getRequiredIndex()
    {
        return $this->requiredIndex;
    }

    /**
     * Set the index of the column in the attributes table that will be used to
     * determine if an attribute has been deleted or not.  Useful when end-users
     * can delete attributes via some kind of GUI.
     *
     * @param string $deletedIndex
     * @return $this
     */
    public function setDeletedIndex($deletedIndex)
    {
        $this->deletedIndex = $deletedIndex;

        return $this;
    }

    /**
     * Get a \Dewdrop\Db\Adapter::describeTable() compatible metadata definition.
     * This allows EAV field's to integrate nicely with the standard Field API.
     *
     * @param string $name
     * @return array
     */
    public function getFieldMetadata($name)
    {
        $attribute = $this->getAttribute($name);

        if (!isset($attribute[$this->requiredIndex])) {
            throw new Exception(
                "The attribute definition does not contain a(n) '{$this->requiredIndex} column to
                indicate whether it is required.  If you're using a different column for that purpose,
                call setRequiredIndex()."
            );
        }

        // @todo Write test that guarantees keys here are equal to keys in describeTable()
        return array(
            'SCHEMA_NAME'      => null,
            'TABLE_NAME'       => $this->table->getTableName(),
            'COLUMN_NAME'      => $name,
            'COLUMN_POSITION'  => null,
            'DATA_TYPE'        => $attribute['backend_type'],
            'GENERIC_TYPE'     => $this->detectGenericType($attribute['backend_type']),
            'DEFAULT'          => $attribute['default_value'],
            'NULLABLE'         => true,
            'LENGTH'           => null,
            'SCALE'            => null,
            'PRECISION'        => null,
            'UNSIGNED'         => true,
            'PRIMARY'          => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY'         => false
        );
    }

    /**
     * Get the full set of attributes supported by this definition.  The
     * array's keys are the attribute field names (e.g. "eav_1" or "eav_22")
     * and the values are the information from the attributes table in the
     * DB.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->loadAttributes();
    }

    /**
     * Get information about the attribute matching the supplied name.
     *
     * @param string $name
     * @return array
     */
    public function getAttribute($name)
    {
        $this->loadAttributes();

        return $this->attributes[$name];
    }

    /**
     * Save the value for the supplied attribute, using the primary key
     * values from the table this EAV definition is registered to.
     *
     * @param string $name The name of the attribute to save (e.g. "eav_1")
     * @param mixed $value
     * @param array $pkeyValues
     */
    public function save($name, $value, array $pkeyValues)
    {
        $db = $this->table->getAdapter();

        $pkeyColumns = $this->table->getPrimaryKey();
        $attribute   = $this->getAttribute($name);
        $valueTable  = $this->getBackendTypeTableName($attribute['backend_type']);
        $valueQuoted = $db->quoteIdentifier($valueTable);

        $where = $this->table->assembleFindWhere($pkeyValues);

        $sql = $db->quoteInto(
            "SELECT true FROM {$valueQuoted} WHERE $where AND attribute_id = ?",
            $attribute['attribute_id']
        );

        if (is_bool($value)) {
            $value = (int) $value;
        }

        if ($db->fetchOne($sql)) {
            $db->update(
                $valueTable,
                array('value' => $value),
                $db->quoteInto("{$where} AND attribute_id = ?", $attribute['attribute_id'])
            );
        } else {
            $data = array(
                'attribute_id' => $attribute['attribute_id'],
                'value'        => $value
            );

            foreach ($pkeyColumns as $i => $name) {
                $data[$name] = $pkeyValues[$i];
            }

            $db->insert($valueTable, $data);
        }
    }

    /**
     * Load the initial value for the supplied attribute name.  If the supplied
     * row is new (i.e. no primary key value), then we use the default value
     * for the attribute.  Otherwise, we query that appropriate value table.
     *
     * @param Row $row
     * @param string $name
     */
    public function loadInitialValue(Row $row, $name)
    {
        $db = $this->table->getAdapter();

        $attribute = $this->getAttribute($name);

        if ($row->isNew()) {
            return $attribute['default_value'];
        } else {
            $valueTable = $this->getBackendTypeTableName($attribute['backend_type']);

            $stmt = $db->select()
                ->from($valueTable, array('value'))
                ->where('attribute_id = ?', $attribute['attribute_id']);

            foreach ($this->table->getPrimaryKey() as $keyColumn) {
                $keyQuoted = $db->quoteIdentifier("{$valueTable}.{$keyColumn}");

                $stmt->where("{$keyQuoted} = ?", $row->get($keyColumn));
            }

            return $db->fetchOne($stmt);
        }
    }

    /**
     * Assemble the name of the value table for the supplied backend type.
     *
     * @param string $backendType
     * @return string
     */
    private function getBackendTypeTableName($backendType)
    {
        return $this->table->getTableName() . $this->valueTablePrefix . $backendType;
    }

    /**
     * Load the list of available attributes from the database, if they haven't
     * been loaded already.  The array of attributes will use the attribute field
     * name (e.g. "eav_1") for a key.
     *
     * @return array
     */
    private function loadAttributes()
    {
        if (!$this->attributes) {
            $stmt = $this->table->select();

            $stmt
                ->from($this->getAttributeTableName());

            if (is_callable($this->attributeFilterCallback)) {
                $stmt = call_user_func($this->attributeFilterCallback, $stmt);
            }

            $out = array();
            $rs  = $this->table->getAdapter()->fetchAll($stmt);

            foreach ($rs as $row) {
                if ($this->deletedIndex && isset($row[$this->deletedIndex]) && $row[$this->deletedIndex]) {
                    continue;
                }

                $name = 'eav_' . $row['attribute_id'];

                $out[$name] = $row;
            }

            $this->attributes = $out;
        }

        return $this->attributes;
    }

    /**
     * Determine a generic data type appropriate for the supplied EAV type.
     * The EAV backend value tables are named after the data types in the DB,
     * so they don't directly match generic types from our PHP API.  This
     * method maps the two domains together so that we can check an EAV
     * field's generic type in our application code.
     *
     * @param string $eavType
     * @return string
     * @throws Exception
     */
    private function detectGenericType($eavType)
    {
        switch ($eavType) {
            case 'datetime':
                return 'timestamp';
            case 'decimal':
                return 'float';
            case 'int':
                return 'integer';
            case 'text':
                return 'clob';
            case 'varchar':
                return 'text';
            case 'boolean':
                return 'boolean';
        }

        throw new Exception("Could not map generic type for EAV backend: {$eavType}.");
    }

    /**
     * Augment the provided Select object with all the EAV attribute values from this
     * definition.
     *
     * @param Select $select
     * @return Select
     * @throws Select
     */
    public function augmentSelect(Select $select)
    {
        $db = $this->table->getAdapter();
        $id = current($this->table->getPrimaryKey());

        $rootTableAlias = $select->quoteWithAlias($this->table->getTableName(), $id);

        foreach ($this->getAttributes() as $attribute) {
            $alias  = 'eav_' . $attribute['attribute_id'];
            $table  = $this->table->getTableName() . $this->valueTablePrefix . $attribute['backend_type'];

            $select->joinLeft(
                [$alias => $table],
                $db->quoteInto(
                    "{$alias}.{$id} = {$rootTableAlias} AND {$alias}.attribute_id = ?",
                    $attribute['attribute_id']
                ),
                [$alias => 'value']
            );
        }

        return $select;
    }
}
