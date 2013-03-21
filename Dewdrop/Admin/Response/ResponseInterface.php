<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Response;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;

/**
 * The interface response classes have to follow to work with the component
 * route and dispatch process.
 */
interface ResponseInterface
{
    /**
     * Set the output generated during the request.
     *
     * @param string $output
     */
    public function setOutput($output);

    /**
     * Set the page object generating the response so it can be interacted
     * with during testing.
     *
     * @param PageAbstract $page
     * @return \Dewdrop\Admin\Response\ResponseInterface
     */
    public function setPage(PageAbstract $page);

    /**
     * Set the response helper associated with this response.
     *
     * @param ResponseHelper $helper
     * @return \Dewdrop\Admin\Response\ResponseInterface
     */
    public function setHelper(ResponseHelper $helper);

    /**
     * Whether the component managing the page dispatch process should
     * render the generated output to the client.
     *
     * @return boolean
     */
    public function shouldRenderOutput();

    /**
     * If the page should be short-circuited, skipping the render step
     * altogether because we decided to redirect or halt execution during
     * process().
     *
     * @return boolean
     */
    public function shouldShortCircuit();

    /**
     * Set whether the page's process method was called.
     *
     * @param boolean $wasProcessed
     * @return \Dewdrop\Admin\Response\ResponseInterface
     */
    public function setWasProcessed($wasProcessed);
}
