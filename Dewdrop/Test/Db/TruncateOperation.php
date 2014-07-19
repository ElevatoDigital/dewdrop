<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Test\Db;

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
        if (defined('WPINC')) {
            $connection->getConnection()->query("SET foreign_key_checks = 0");
        }

        foreach ($dataSet->getReverseIterator() as $table) {
            /* @var $table PHPUnit_Extensions_Database_DataSet_ITable */
            $query = "
                {$connection->getTruncateCommand()}
                {$connection->quoteSchemaObject($table->getTableMetaData()->getTableName())}
            ";

            if (defined('WPINC')) {
                $query .= "";
            } else {
                $query .= " RESTART IDENTITY CASCADE";
            }

            try {
                $connection->getConnection()->query($query);
            } catch (PDOException $e) {
                throw new PHPUnit_Extensions_Database_Operation_Exception(
                    'TRUNCATE',
                    $query,
                    array(),
                    $table,
                    $e->getMessage()
                );
            }
        }

        if (defined('WPINC')) {
            $connection->getConnection()->query("SET foreign_key_checks = 1");
        }
    }
}
