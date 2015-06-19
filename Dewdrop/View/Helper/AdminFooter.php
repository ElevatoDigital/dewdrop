<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

/**
 * A placeholder used in the stock Silex admin layout to add content just
 * before the closing body tag.  Does nothing by default, but you can
 * override this helper to inject content your application needs into the
 * admin layout.
 */
class AdminFooter extends AbstractHelper
{
    /**
     * No-op in Dewdrop core.
     *
     * @return string
     */
    public function direct()
    {
        return '';
    }
}
