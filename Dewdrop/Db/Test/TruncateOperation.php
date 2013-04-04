<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Test;

/**
 * A custom truncate operation that works with tables that are referenced by
 * foreign key constraints.
 */
class TruncateOperation extends \PHPUnit_Extensions_Database_Operation_Truncate
{
    /**
     * Disable foreign key constraint checking prior to running the stock PHPUnit
     * truncate operation and then re-enable it.
     *
     * @param \PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
     * @param \PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
     * @return void
     */
    public function execute(
        \PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection,
        \PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
    ) {
        $connection->getConnection()->query("SET foreign_key_checks = 0");
        parent::execute($connection, $dataSet);
        $connection->getConnection()->query("SET foreign_key_checks = 1");
    }
}
