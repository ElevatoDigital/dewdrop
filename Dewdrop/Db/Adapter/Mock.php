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

/**
 * A mock adapter that uses a mock wpdb connection with no actual
 * connection to the database.  Can be used when testing to ensure certain
 * methods are called when you expecte them to be without actually hitting
 * the MySQL database directly.
 */
class Mock extends WpdbAdapter
{
    /**
     * Override default adapter constructor because we no longer need to accept
     * $wpdb when just using a mock adapter for testing.
     */
    public function __construct()
    {
        $this->wpdb = new MockWpdb();
    }
}
