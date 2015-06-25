<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Adapter;

use Dewdrop\Db\Adapter as WpdbAdapter;
use Dewdrop\Db\Driver\Mock as MockDriver;

/**
 * A mock adapter that uses a mock wpdb connection with no actual
 * connection to the database.  Can be used when testing to ensure certain
 * methods are called when you expecte them to be without actually hitting
 * the MySQL database directly.
 */
class Mock extends WpdbAdapter
{
    /**
     * The driver used by this adapter to talk directly to the RDBMS.
     *
     * @var \Dewdrop\Db\Driver\DriverInterface
     */
    protected $driver;

    /**
     * Override default adapter constructor because we no longer need to accept
     * $wpdb when just using a mock adapter for testing.
     *
     * @param MockDriver $driver
     */
    public function __construct($driver = null)
    {
        $this->driver = new MockDriver($this);
    }
}
