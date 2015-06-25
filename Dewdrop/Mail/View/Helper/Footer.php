<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Mail\View\Helper;

/**
 * Render a footer area for things like unsubscribe links.
 */
class Footer extends AbstractMailHelper
{
    /**
     * Open the footer area.
     *
     * @return string
     */
    public function open()
    {
        return $this->partial('footer-open.phtml', array());
    }

    /**
     * Close the footer area.
     *
     * @return string
     */
    public function close()
    {
        return $this->partial('footer-close.phtml', array());
    }
}
