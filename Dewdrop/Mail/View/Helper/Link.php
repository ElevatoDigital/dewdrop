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
 * Render a link (&lt;a&gt;) tag with all the needed inline styles.
 */
class Link extends AbstractMailHelper
{
    /**
     * Render the link with the supplied content inside it.
     *
     * @param string $content
     * @param string $href
     * @return string
     */
    public function direct($content, $href)
    {
        return $this->partial(
            'link.phtml',
            array(
                'content' => $content,
                'href'    => $href
            )
        );
    }
}
