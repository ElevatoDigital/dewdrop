<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

/**
 * Display the "wrap" div around the primary content of your page.  Without
 * "wrap", WP admin pages don't look quite right.  This helper will also
 * roll in admin notices automatically (e.g. success messages following an
 * edit form being successfully processed).
 */
class Wrap extends AbstractHelper
{
    /**
     * Open the wrap div and include the output of the WpAdminNotice helper.
     *
     * @return string
     */
    public function open()
    {
        return $this->partial(
            'wrap-open.phtml',
            array(
                'notice' => $this->view->adminNotice()
            )
        );
    }

    /**
     * Close the wrap div.  No need for a partial view for something this
     * simple.
     *
     * @return string
     */
    public function close()
    {
        return '</div></div>';
    }
}
