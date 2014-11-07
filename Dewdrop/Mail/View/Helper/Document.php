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
 * This helper renders the HTML document's header and footer.  Just call its
 * open() and close() methods to wrap the remainder of your message's view script.
 */
class Document extends AbstractMailHelper
{
    /**
     * Open the HTML document, including the supplied title in the &lt;title&gt; tag.
     *
     * @param string $title
     * @return string
     */
    public function open($title)
    {
        return $this->partial(
            'document-open.phtml',
            array(
                'title' => $title
            )
        );
    }

    /**
     * Close the HTML document.
     *
     * @return string
     */
    public function close()
    {
        return $this->partial('document-close.phtml', array());
    }
}
