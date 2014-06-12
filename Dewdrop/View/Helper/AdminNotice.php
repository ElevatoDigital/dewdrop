<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Pimple;

/**
 * Display an admin notice.  These notices will look just like the normal
 * WP messages you see, for example, when activating and deactivating
 * plugins.
 */
class AdminNotice extends AbstractHelper
{
    /**
     * Display a notice with the supplied notice text and type.  If no text
     * is supplied directly, we'll check cookies with the
     * loadNoticeFromCookies() method and use that text.  If there is still
     * no text, we assume that we're being called implicitly by another
     * helper like WP wrap and that there are no notices to display.  In
     * that case, we just return an empty string.
     *
     * @param string $notice
     * @param string $type
     * @return string
     */
    public function direct($notice = '', $type = 'updated')
    {
        if ('' === $notice) {
            $notice = $this->loadNoticeFromCookies();
        }

        // No notice available, just return empty string
        if (!$notice) {
            return '';
        }

        return $this->partial(
            'admin-notice.phtml',
            array(
                'notice' => $notice,
                'type'   => $type
            )
        );
    }

    /**
     * Check cookies to see if any cross-request notices were set.
     *
     * Right now, we only support the "dewdrop_admin_success_notice" cookie,
     * which is set by the response helper object after an edit form, for
     * example, is successfully processed.  We may at some point offer a
     * more flexible way of passing messages through a session API.
     *
     * @return string
     */
    private function loadNoticeFromCookies()
    {
        if (!Pimple::getResource('paths')->isWp()) {
            return '';
        }

        $notice = '';

        if (isset($_COOKIE['dewdrop_admin_success_notice']) && $_COOKIE['dewdrop_admin_success_notice']) {
            $notice = $_COOKIE['dewdrop_admin_success_notice'];

            // Expire/delete cookie
            setcookie('dewdrop_admin_success_notice', null, 0);
        }

        return $notice;
    }
}
