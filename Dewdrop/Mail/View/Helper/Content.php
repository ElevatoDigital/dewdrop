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
 * This helper renders the white content area that contains the bulk of the
 * message's actual content.  You'd typically open this just after opening
 * the document itself.  You may want to include some supplemental content
 * after closing this helper but before closing the document, however (e.g.
 * an unsubscribe link).
 */
class Content extends AbstractMailHelper
{
    /**
     * Open the content or "page" area.
     *
     * @return string
     */
    public function open()
    {
        return $this->partial('content-open.phtml', array());
    }

    /**
     * Close the content area.
     *
     * @return string
     */
    public function close()
    {
        return $this->partial('content-close.phtml', array());
    }
}
