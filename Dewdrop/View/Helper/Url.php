<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Pimple;

class Url extends AbstractHelper
{
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
