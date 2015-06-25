<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Exception;

/**
 * Render some breadcrumbs using Bootstrap's markup.
 */
class BootstrapBreadcrumbs extends AbstractHelper
{
    /**
     * Your crumbs array should contain an array for each crumb.  Every
     * crumb must have a title and if your crumb is not active, it must
     * also contain an href.
     *
     * @param array $crumbs
     * @return string
     * @throws Exception
     */
    public function direct(array $crumbs)
    {
        foreach ($crumbs as $index => $crumb) {
            if (!isset($crumb['title'])) {
                throw new Exception('Each crumb must have a title.');
            }

            if (!isset($crumb['active'])) {
                $crumb['active'] = false;
            }

            if (!$crumb['active'] && !isset($crumb['active'])) {
                throw new Exception('Inactive crumbs must have an href.');
            }

            $crumbs[$index] = $crumb;
        }

        return $this->partial(
            'bootstrap-breadcrumbs.phtml',
            [
                'crumbs' => $crumbs
            ]
        );
    }
}
