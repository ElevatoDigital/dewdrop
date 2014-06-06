<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Driver\Pdo;

use Dewdrop\Db\Adapter;
use Dewdrop\Db\Driver\DriverInterface;
use Dewdrop\Exception;
use PDO;
use PDOException;

/**
 * This class provides a driver based upon a Postgres PDO connection
 * object.
 */
class Pgsql implements DriverInterface
{
    /**
     * The Dewdrop DB adapter associated with this driver.
     *
     * @var \Dewdrop\Db\Adapter
     */
    private $adapter;

    /**
     * The PDO connection used to talk to Postgres.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Associate this driver with the provided adapter and PDO connection.
     *
     * @param Adapter $adapter
     * @param PDO $pdo
     */
    public function __construct(Adapter $adapter, PDO $pdo)
    {
        $this->adapter = $adapter;
        $this->pdo     = $pdo;

        $this->adapter->setDriver($this);
    }

    /**
     * Retrieve the raw connection object used by this driver.  For example,
     * this could be a wpdb object or a PDO connection object.
     *
     * @return mixed
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Fetch all results for the supplied SQL query.
     *
     * The SQL query can be a simple string or a Select object.  The bind array
     * should supply values for all the parameters, either named or numeric, in
     * the query.  And the fetch mode should match one of these 4 class constants
     * from \Dewdrop\Db\Adapter: ARRAY_A, ARRAY_N, OBJECT, or OBJECT_K.
     *
     * @param mixed $sql
     * @param array $bind
     * @param string $fetchMode
     * @return array
     */
    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
        return $this->query($sql, $bind, $fetchMode);
    }

    /**
     * Fetch a single column of the results from the supplied SQL statement.
     *
     * @param string|\Dewdrop\Db\Select $sql
     * @param array $bind
     * @return array
     */
    public function fetchCol($sql, $bind = array())
    {
        $out = array();
        $rs  = $this->fetchAll($sql, $bind, Adapter::ARRAY_N);

        foreach ($rs as $row) {
            $out[] = $row[0];
        }

        return $out;
    }

    /**
     * Fetch a single scalar value from the results of the supplied SQL
     * statement.
     *
     * @param string|\Dewdrop\Db\Select $sql
     * @param array $bind
     * @return mixed
     */
    public function fetchOne($sql, $bind = array())
    {
        $result = $this->fetchAll($sql, $bind, Adapter::ARRAY_N);

        if ($result) {
            return $result[0][0];
        }

        return null;
    }

    /**
     * Run the supplied query, binding the supplied data to the statement
     * prior to execution.
     *
     * @param string|\Dewdrop\Db\Select $sql
     * @param array $bind
     * @param string $fetchMode
     * @return mixed
     */
    public function query($sql, $bind = array(), $fetchMode = null)
    {
        if (is_array($bind) && count($bind)) {
            $i = 0;

            foreach ($bind as $name => $value) {
                if (!is_int($name) && !preg_match('/^:/', $name)) {
                    unset($bind[$name]);

                    $name = ':' . $name;
                }

                if (false === stripos($sql, $name)) {
                    unset($bind[$name]);

                    $name = $i;
                }

                $bind[$name] = $value;

                $i += 1;
            }
        }

        try {
            $statement = $this->pdo->prepare($sql);
            $result    = $statement->execute($bind);

            if (false === $result) {
                $errorInfo = $statement->errorInfo();

                throw new Exception($errorInfo[2] . ' (' . $sql . ')');
            }

            if (preg_match('/^(INSERT|UPDATE|DELETE) /i', $sql)) {
                return $statement->rowCount();
            } else {
                $pdoFetchMode  = PDO::FETCH_ASSOC;
                $pdoFetchClass = null;

                if (Adapter::ARRAY_N === $fetchMode) {
                    $pdoFetchMode = PDO::FETCH_NUM;
                } elseif (Adapter::OBJECT_K === $fetchMode) {
                    $pdoFetchClass = 'stdClass';
                } elseif (Adapter::OBJECT === $fetchMode) {
                    $pdoFetchMode  = PDO::FETCH_NUM;
                    $pdoFetchClass = 'stdClass';
                }

                if ($pdoFetchClass) {
                    return $statement->fetchAll($pdoFetchMode, $pdoFetchClass);
                } else {
                    return $statement->fetchAll($pdoFetchMode);
                }
            }
        } catch (PDOException $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * Get the last insert ID from the driver after performing an insert on a table
     * with an auto-incrementing primary key.
     *
     * @return integer
     */
    public function lastInsertId()
    {
        $table = $this->adapter->getLastInsertTableName();

        // No table name available, so let's bail
        if (!$table) {
            return null;
        }

        $meta = $this->adapter->getTableMetadata($table);

        if ($meta) {
            foreach ($meta['columns'] as $name => $columnMeta) {
                if ($columnMeta['PRIMARY'] && $columnMeta['IDENTITY']) {
                    return $this->fetchOne(
                        'SELECT CURRVAL(PG_GET_SERIAL_SEQUENCE(?, ?))',
                        array($table, $name)
                    );
                }
            }
        }

        return null;
    }

    /**
     * Returns the symbol the adapter uses for delimited identifiers.
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return '"';
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        $sql = "SELECT c.relname AS table_name "
             . "FROM pg_class c, pg_user u "
             . "WHERE c.relowner = u.usesysid AND c.relkind = 'r' "
             . "AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
             . "AND c.relname !~ '^(pg_|sql_)' "
             . "UNION "
             . "SELECT c.relname AS table_name "
             . "FROM pg_class c "
             . "WHERE c.relkind = 'r' "
             . "AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
             . "AND NOT EXISTS (SELECT 1 FROM pg_user WHERE usesysid = c.relowner) "
             . "AND c.relname !~ '^pg_'";

        return $this->fetchCol($sql);
    }

    /**
     * Returns an associative array containing all the foreign key relationships
     * associated with the supplied table.
     *
     * The array has the following format:
     *
     * <code>
     * array(
     *     'column_name' => array(
     *         'table'  => 'foreign_table',
     *         'column' => 'foreign_column'
     *     )
     * )
     * </code>
     *
     * @param string $tableName
     * @return array
     */
    public function listForeignKeyReferences($tableName)
    {
        $sql = "SELECT
                    tc.constraint_name, tc.table_name, kcu.column_name,
                    ccu.table_name AS foreign_table_name,
                    ccu.column_name AS foreign_column_name
                FROM information_schema.table_constraints AS tc
                JOIN information_schema.key_column_usage AS kcu
                    ON tc.constraint_name = kcu.constraint_name
                JOIN information_schema.columns AS co
                    ON co.table_name = tc.table_name
                        AND kcu.column_name = co.column_name
                JOIN information_schema.constraint_column_usage AS ccu
                    ON ccu.constraint_name = tc.constraint_name
                WHERE
                    constraint_type = 'FOREIGN KEY'
                    AND tc.table_name = ?
                ORDER BY co.ordinal_position;";

        $result = $this->fetchAll($sql, array($tableName));
        $refs   = array();

        foreach ($result as $row) {
            $column = $row['column_name'];

            $refs[$column] = array(
                'table'  => $row['foreign_table_name'],
                'column' => $row['foreign_column_name']
            );
        }

        return $refs;
    }

    /**
     * Returns an associative array containing all the unique constraints on a table.
     *
     * The array has the following format:
     *
     * <code>
     * array(
     *     'key_name' => array(
     *         sequence_in_index => 'column_name'
     *     )
     * )
     * </code>
     *
     * @param string $tableName
     * @return array
     */
    public function listUniqueConstraints($tableName)
    {
        $sql = "SELECT
                    co.oid,
                    a.attname
                FROM pg_constraint co
                JOIN pg_class cl ON cl.oid = co.conrelid
                JOIN pg_namespace n ON cl.relnamespace = n.oid
                JOIN pg_attribute a ON a.attrelid = cl.oid
                WHERE
                    cl.relname = ?
                    AND co.contype = 'u'
                    AND a.attnum = ANY(co.conkey)";

        $sql = $this->adapter->quoteInto($sql, $tableName);

        $sql .= ' ORDER BY co.oid, a.attname';

        $result      = $this->fetchAll($sql, array(), Adapter::ARRAY_A);
        $constraints = array();

        foreach ($result as $row) {
            if (! array_key_exists($row['oid'], $constraints)) {
                $constraints[$row['oid']] = array();
            }

            $constraints[$row['oid']][] = $row['attname'];
        }

        return $constraints;
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @return array
     */
    public function describeTable($tableName)
    {
        $sql = "SELECT
                a.attnum,
                n.nspname,
                c.relname,
                a.attname AS colname,
                t.typname AS type,
                a.atttypmod,
                FORMAT_TYPE(a.atttypid, a.atttypmod) AS complete_type,
                d.adsrc AS default_value,
                a.attnotnull AS notnull,
                a.attlen AS length,
                co.contype,
                ARRAY_TO_STRING(co.conkey, ',') AS conkey
            FROM pg_attribute AS a
                JOIN pg_class AS c ON a.attrelid = c.oid
                JOIN pg_namespace AS n ON c.relnamespace = n.oid
                JOIN pg_type AS t ON a.atttypid = t.oid
                LEFT OUTER JOIN pg_constraint AS co ON (co.conrelid = c.oid
                    AND a.attnum = ANY(co.conkey) AND co.contype = 'p')
                LEFT OUTER JOIN pg_attrdef AS d ON d.adrelid = c.oid AND d.adnum = a.attnum
            WHERE a.attnum > 0 AND c.relname = ".$this->adapter->quote($tableName)
            . ' ORDER BY a.attnum';

        $result = $this->fetchAll($sql, array(), Adapter::ARRAY_N);

        $attnum        = 0;
        $nspname       = 1;
        $relname       = 2;
        $colname       = 3;
        $type          = 4;
        $atttypemod    = 5;
        $complete_type = 6;
        $default_value = 7;
        $notnull       = 8;
        $length        = 9;
        $contype       = 10;
        $conkey        = 11;

        $desc = array();
        foreach ($result as $key => $row) {
            $defaultValue = $row[$default_value];
            if ($row[$type] == 'varchar' || $row[$type] == 'bpchar' ) {
                if (preg_match('/character(?: varying)?(?:\((\d+)\))?/', $row[$complete_type], $matches)) {
                    if (isset($matches[1])) {
                        $row[$length] = $matches[1];
                    } else {
                        $row[$length] = null; // unlimited
                    }
                }
                if (preg_match("/^'(.*?)'::(?:character varying|bpchar)$/", $defaultValue, $matches)) {
                    $defaultValue = $matches[1];
                }
            }
            list($primary, $primaryPosition, $identity) = array(false, null, false);
            if ($row[$contype] == 'p') {
                $primary = true;
                $primaryPosition = array_search($row[$attnum], explode(',', $row[$conkey])) + 1;
                $identity = (bool) (preg_match('/^nextval/', $row[$default_value]));
            }
            $desc[$this->adapter->foldCase($row[$colname])] = array(
                'SCHEMA_NAME'      => $this->adapter->foldCase($row[$nspname]),
                'TABLE_NAME'       => $this->adapter->foldCase($row[$relname]),
                'COLUMN_NAME'      => $this->adapter->foldCase($row[$colname]),
                'COLUMN_POSITION'  => $row[$attnum],
                'DATA_TYPE'        => $row[$type],
                'DEFAULT'          => $defaultValue,
                'NULLABLE'         => (bool) ($row[$notnull] != 't'),
                'LENGTH'           => $row[$length],
                'SCALE'            => null, // @todo
                'PRECISION'        => null, // @todo
                'UNSIGNED'         => null, // @todo
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity
            );
        }

        return $desc;
    }

    public function beginTransaction()
    {
        return $this->query('BEGIN');
    }

    public function commit()
    {
        return $this->query('COMMIT');
    }
}
