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
 * Render an H2.
 */
class HeaderTwo extends AbstractMailHelper
{
    /**
     * Render an H2 with the supplied content.
     *
     * @param string $content
     * @return string
     */
    public function direct($content)
    {
        return $this->partial(
            'header-two.phtml',
            array(
                'content' => $content
            )
        );
    }
}
