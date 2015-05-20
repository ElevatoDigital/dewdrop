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
 * Render a big call-to-action button.
 */
class Button extends AbstractMailHelper
{
    /**
     * Render the button with the supplied content inside it and href.
     *
     * @param string $content
     * @param string $href
     * @return string
     */
    public function direct($content, $href)
    {
        $escapedHref = $this->view->escapeHtmlAttr($href);

        return $this->partial(
            'button.phtml',
            array(
                'content' => $content,
                'href'    => $escapedHref
            )
        );
    }
}
