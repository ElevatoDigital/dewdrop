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
 * Render a paragraph tag with all the needed inline styles.
 */
class Paragraph extends AbstractMailHelper
{
    /**
     * Render the paragraph with the supplied content inside it.
     *
     * @param string $content
     * @return string
     */
    public function direct($content)
    {
        return $this->partial(
            'paragraph.phtml',
            array(
                'content' => $content
            )
        );
    }
}
