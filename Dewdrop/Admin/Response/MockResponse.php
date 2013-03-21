<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Response;

use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;
use Dewdrop\Admin\Page\PageAbstract;

/**
 * A response class for use during testing.  Will tell the managing component
 * to not actually render the output and will not actually execute any helper
 * actions, just check to see if they _would_ be executed in the browser.
 */
class MockResponse extends Response
{
    /**
     * Whether to render output after dispatching.  Never for the mock
     * response.
     *
     * @return boolean
     */
    public function shouldRenderOutput()
    {
        return false;
    }

    /**
     * If the page should be short-circuited, skipping the render step
     * altogether because we decided to redirect or halt execution during
     * process().
     *
     * @return boolean
     */
    public function shouldShortCircuit()
    {
        return $this->helper->hasRedirectUrl();
    }
}
