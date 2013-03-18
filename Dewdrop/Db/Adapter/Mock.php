<?php

namespace Dewdrop\Db\Adapter;

use Dewdrop\Db\Adapter as WpdbAdapter;

/**
 * A mock adapter that uses a mock wpdb connection with no actual
 * connection to the database.  Can be used when testing to ensure certain
 * methods are called when you expecte them to be without actually hitting
 * the MySQL database directly.
 *
 * @category   Dewdrop
 * @package    Db
 * @subpackage Adapter
 */
class Mock extends WpdbAdapter
{
    public function __construct()
    {
        $this->wpdb = new MockWpdb();
    }
}
