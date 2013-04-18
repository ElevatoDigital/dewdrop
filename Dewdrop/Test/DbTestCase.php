<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Test;

use Dewdrop\Test\Db\TruncateOperation;
use PHPUnit_Extensions_Database_TestCase;
use PDO;
use Zend\Dom\Query as DomQuery;

/**
 * This class be extended in your PHPUnit tests to provide PHPUnit with a
 * PDO-basd connection to the WP MySQL database for loading testing fixtures
 * and comparing them with your expected test results.
 */
abstract class DbTestCase extends PHPUnit_Extensions_Database_TestCase implements DomInterface
{
    /**
     * Create the PDO connection for PHPUnit using constants defined in
     * wp-config.php.
     *
     * @return PDO
     */
    final public function getConnection()
    {
        $connection = new PDO(
            'mysql:dbname=' . DB_NAME . ';host=' . DB_HOST,
            DB_USER,
            DB_PASSWORD
        );

        return $this->createDefaultDBConnection($connection, DB_NAME);
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
        $cascadeTruncates = true;

        return new \PHPUnit_Extensions_Database_Operation_Composite(
            array(
                new TruncateOperation($cascadeTruncates),
                \PHPUnit_Extensions_Database_Operation_Factory::INSERT()
            )
        );
    }

    /**
     * Assert that the supplied CSS selector matches the supplied HTML.
     *
     * This implementation duplicates the implementation in the Base test class
     * because we are limiting ourselves to 5.3, so we have no way of using
     * traits to reuse this code while still extending PHPUnit's DBUnit
     * class.
     *
     * @param string $selector A CSS selected.
     * @param string $html The HTML you are selecting against.
     * @return void
     */
    public function assertMatchesDomQuery($selector, $html)
    {
        $results = $this->queryDom($selector, $html);

        $this->assertTrue(
            count($results) > 0,
            "The HTML output does not match the DOM query \"{$selector}\"."
        );
    }

    /**
     * Use the supplied CSS selector to query the HTML.  Returns the results
     * as a \Zend\Dom\NodeList, which can be iterated over to inspect the
     * resulting DOMElement objects as needed.
     *
     * This implementation duplicates the implementation in the Base test class
     * because we are limiting ourselves to 5.3, so we have no way of using
     * traits to reuse this code while still extending PHPUnit's DBUnit
     * class.
     *
     * @param string $selector
     * @param string $html
     * @return \Zend\Dom\NodeList
     */
    public function queryDom($selector, $html)
    {
        $query = new DomQuery($html);
        return $query->execute($selector);
    }
}
