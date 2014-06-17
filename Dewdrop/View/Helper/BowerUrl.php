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
 * This helper returns a URL prefixed with the path to the bower_components
 * folder for your application.  This will vary between WP and Silex projects,
 * so this helper hides that difference.
 */
class BowerUrl extends AbstractHelper
{
    /**
     * If no $wwwPath or $docRoot are provided, pull those from the environment.
     * Return the supplied URL with a prefix pointing to the bower_components
     * folder, being careful to not double-up on slashes on either end.
     *
     * @param string $url
     * @param string $wwwPath
     * @param string $docRoot
     * @return string
     */
    public function direct($url, $wwwPath = null, $docRoot = null)
    {
        if (null === $wwwPath) {
            $paths   = Pimple::getResource('paths');
            $docRoot = ($docRoot ?: $_SERVER['DOCUMENT_ROOT']);
            $wwwPath = '/' . trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $paths->getWww()), '/') . '/';
        }

        return $wwwPath . ltrim($url, '/');
    }
}
