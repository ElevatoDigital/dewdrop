<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Zend\View\Helper\HeadLink;
use Zend\View\Helper\HeadScript;

/**
 * This helper can be used to prepend a string to URLs to assist with busting
 * HTTP caches when pushing a new build or working in development.  Used in
 * conjunction with a rewrite rule that strips the cache prefix before looking
 * for the file on disk, it allows you to avoid issues caused by stale caches
 * without having to rename the actual files on disk.
 *
 * In Apache, for example, you could add the following mod_rewrite rule:
 *
 * <pre>
 * RewriteEngine On
 *
 * # Strip cache-busting prefixes from URLs
 * RewriteRule ^cache-[\dA-Z.\-_]+/(.+) $1 [L,NC]
 * </pre>
 *
 * And then set the prefix of this helper to cache-build-1234.  When you
 * adjust your prefix to cache-build-1235, all the URLs filtered by this
 * helper will change, causing HTTP caches to be invalidated.  With this
 * URL invalidation setup in place, you can also use far-future expires
 * headers for your static content, which will improve performance
 * significantly.  In Apache, you can set those headers with this addition
 * to your .htaccess:
 *
 * <pre>
 * ExpiresActive On
 * ExpiresByType text/html "access plus 1 seconds"
 * ExpiresByType image/gif "access plus 10 years"
 * ExpiresByType image/jpeg "access plus 10 years"
 * ExpiresByType image/png "access plus 10 years"
 * ExpiresByType text/css "access plus 10 years"
 * ExpiresByType text/javascript "access plus 10 years"
 * ExpiresByType application/javascript "access plus 10 years"
 * ExpiresByType application/x-javascript "access plus 10 years"
 * </pre>
 */
class UrlCachePrefix extends AbstractHelper
{
    /**
     * The prefix to prepend to all URLs filtered by this helper.
     *
     * @var string
     */
    private $prefix = '';

    /**
     * The helper instance or, if a string is supplied as the first parameter,
     * the filtered URL.
     *
     * @return $this|string
     */
    public function direct()
    {
        $args = func_get_args();

        if (0 === count($args)) {
            return $this;
        } else {
            return $this->url($args[0]);
        }
    }

    /**
     * Prepend the prefix to the supplied URL if it doesn't start with a protocol
     * designation or "//" (the http/https agnostic URL prefix).
     *
     * @param $url
     * @return string
     */
    public function url($url)
    {
        if (!$this->prefix || 0 === strpos($url, '//') || preg_match('/^[A-Z\d]+?:/i', $url)) {
            return $url;
        }

        return '/' . $this->prefix . '/' . ltrim($url, '/');
    }

    /**
     * Set the prefix you'd like to prepend to filtered URLs.  Typically, you'll
     * set this in your Pimple definition of your view object in your application's
     * bootstrap so that it is available consistently throughout your app.
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = trim($prefix, '/');

        return $this;
    }

    /**
     * Get the prefix that should be used for cached URLs.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Modify all URLs in the supplied HeadLink view helper to include the URL
     * prefix and then return it.
     *
     * @param HeadLink $headLink
     * @return HeadLink
     */
    public function headLink(HeadLink $headLink)
    {
        foreach ($headLink as $item) {
            if (isset($item->href)) {
                $item->href = $this->url($item->href);
            }
        }

        return $headLink;
    }

    /**
     * Modify all URLs in the supplied HeadLink view helper to include the URL
     * prefix and then return it.
     *
     * @param HeadScript $headScript
     * @return HeadScript
     */
    public function headScript(HeadScript $headScript)
    {
        foreach ($headScript as $item) {
            if (isset($item->attributes) && isset($item->attributes['src'])) {
                $item->attributes['src'] = $this->url($item->attributes['src']);
            }
        }

        return $headScript;
    }
}
