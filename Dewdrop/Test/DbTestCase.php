<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Test;

use Dewdrop\Pimple;
use Dewdrop\Test\Db\TruncateOperation;
use PHPUnit_Extensions_Database_TestCase;
use PDO;

/**
 * This class be extended in your PHPUnit tests to provide PHPUnit with a
 * PDO-basd connection to the WP MySQL database for loading testing fixtures
 * and comparing them with your expected test results.
 */
abstract class DbTestCase extends PHPUnit_Extensions_Database_TestCase
{
    /**
     * Use DomAssertions trait to bring in custom assertions.
     */
    use DomAssertions;

    /**
     * Create the PDO connection for PHPUnit using constants defined in
     * wp-config.php.
     *
     * @return PDO
     */
    final public function getConnection()
    {
        if (!defined('WPINC')) {
            return $this->createDefaultDBConnection(
                Pimple::getResource('db')->getConnection(),
                Pimple::getResource('config')['db']['name']
            );
        } else {
            $connection = new PDO(
                'mysql:dbname=' . DB_NAME . ';host=' . DB_HOST,
                DB_USER,
                DB_PASSWORD
            );

            return $this->createDefaultDBConnection($connection, DB_NAME);
        }
    }

    /**
     * Use our own truncate operation so that we can work with InnoDB foreign
     * keys constraints.
     *
     * @see http://stackoverflow.com/questions/10331445/phpunit-and-mysql-truncation-error
     * @return \PHPUnit_Extensions_Database_Operation_Composite
     */
    public function getSetUpOperation()
    {
        return new \PHPUnit_Extensions_Database_Operation_Composite(
            array(
                new TruncateOperation(true),
                \PHPUnit_Extensions_Database_Operation_Factory::INSERT()
            )
        );
    }
}
