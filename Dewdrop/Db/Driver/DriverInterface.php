<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Driver;

interface DriverInterface
{
    public function getConnection();

    public function fetchAll($sql, $bind = array(), $fetchMode = null);

    public function fetchCol($sql, $bind = array());

    public function fetchOne($sql, $bind = array());

    public function query($sql, $bind = array());

    public function lastInsertId();

    public function getQuoteIdentifierSymbol();

    public function listTables();

    public function listForeignKeyReferences($tableName);

    public function listUniqueConstraints($tableName);

    public function describeTable($tableName);
}
