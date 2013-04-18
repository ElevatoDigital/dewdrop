<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Test;

interface DomInterface
{
    /**
     * Assert that the supplied CSS selector matches the supplied HTML.
     *
     * @param string $selector A CSS selected.
     * @param string $html The HTML you are selecting against.
     * @return void
     */
    public function assertMatchesDomQuery($selector, $html);

    /**
     * Use the supplied CSS selector to query the HTML.  Returns the results
     * as a \Zend\Dom\NodeList, which can be iterated over to inspect the
     * resulting DOMElement objects as needed.
     *
     * @param string $selector
     * @param string $html
     * @return \Zend\Dom\NodeList
     */
    public function queryDom($selector, $html);
}
