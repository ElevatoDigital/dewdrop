<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Pimple;

/**
 * A view helper that will filter any supplied URL through the
 * url-filter resource in Pimple, if present.  Allows you to manipulate
 * URLs in your application to prefix them in some way, for example.
 */
class Url extends AbstractHelper
{
    /**
     * Filter the supplied URL.
     *
     * @param string $url
     * @return mixed
     */
    public function direct($url)
    {
        if (!Pimple::hasResource('url-filter')) {
            return $url;
        } else {
            /* @var $filter callable */
            $filter = Pimple::getResource('url-filter');
            return $filter($url);
        }
    }
}
