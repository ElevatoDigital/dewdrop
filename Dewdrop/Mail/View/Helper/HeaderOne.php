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
 * Render an H1.
 */
class HeaderOne extends AbstractMailHelper
{
    /**
     * Render an H1 with the supplied content.
     *
     * @param string $content
     * @return string
     */
    public function direct($content)
    {
        return $this->partial(
            'header-one.phtml',
            array(
                'content' => $content
            )
        );
    }
}
