<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Adapter;

/**
 * A mock wpdb class that doesn't ever interact with the DB directly.  It
 * simply lets you detect when certain methods are called during testing
 * to ensure application logic is functioning as expected.
 */
class MockWpdb
{
    /**
     * Prevent any errors from being generated while testing by providing
     * a generic fallback for all wpdb calls.
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        return $this;
    }
}
