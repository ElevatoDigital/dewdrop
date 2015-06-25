<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

/**
 * Render an HTML table that uses markup and CSS classes that should help it
 * blend in nicely with the WP admin.
 */
class WpTable extends Table
{
    /**
     * Open the table with the set of classes typically used in the WP admin.
     *
     * @return string
     */
    public function open()
    {
        return '<table class="wp-list-table widefat fixed posts" cellspacing="0">';
    }
}
