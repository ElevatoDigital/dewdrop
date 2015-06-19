<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Admin\Env\Silex as SilexEnv;

/**
 * This view helper renders the admin area's title in the stock Silex admin
 * environment navbar.  You can override this helper if you want to use
 * different markup/content for your admin title.
 */
class AdminTitle extends AbstractHelper
{
    /**
     * Render the admin title using Bootstrap's navbar-brand markup.
     *
     * @param SilexEnv $env
     * @return string
     */
    public function direct(SilexEnv $env)
    {
        return sprintf(
            '<a class="navbar-brand" href="%s">%s</a>',
            $this->view->escapeHtmlAttr($this->view->url('/admin/')),
            $this->view->escapeHtml($env->getTitle())
        );
    }
}
