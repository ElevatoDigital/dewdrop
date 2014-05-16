<?php

namespace Dewdrop\Db\Driver\Pdo;

use Dewdrop\Db\Adapter;
use Dewdrop\Db\Driver\DriverInterface;
use Dewdrop\Exception;
use PDO;
use PDOException;

class Pgsql implements DriverInterface
{
    private $adapter;

    private $pdo;

    public function __construct(Adapter $adapter, PDO $pdo)
    {
        $this->adapter = $adapter;
        $this->pdo     = $pdo;

        $this->adapter->setDriver($this);
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
        return $this->query($sql, $bind, $fetchMode);
    }

    public function fetchCol($sql, $bind = array())
    {
        $out = array();
        $rs  = $this->fetchAll($sql, $bind, Adapter::ARRAY_N);

        foreach ($rs as $row) {
            $out[] = $row[0];
        }

        return $out;
    }

    public function fetchOne($sql, $bind = array())
    {
        $result = $this->fetchAll($sql, $bind, Adapter::ARRAY_N);

        if ($result) {
            return $result[0][0];
        }

        return null;
    }

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

    public function getQuoteIdentifierSymbol()
    {
        return '"';
    }

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
}
