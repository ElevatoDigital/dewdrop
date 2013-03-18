<?php

namespace Dewdrop\Db\Adapter;

/**
 * A mock wpdb class that doesn't ever interact with the DB directly.  It
 * simply lets you detect when certain methods are called during testing
 * to ensure application logic is functioning as expected.
 *
 * @category   Dewdrop
 * @package    Db
 * @subpackage Adapter
 */
class MockWpdb
{
    public function __call($method, $args)
    {

    }
}
