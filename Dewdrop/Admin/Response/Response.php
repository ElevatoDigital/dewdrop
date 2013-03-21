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
 * This is the standard response implementation.  It will actually
 * execute helper actions that were setup during process and will
 * tell the controlling component to render the response to the
 * client.
 */
class Response implements ResponseInterface
{
    /**
     * The output generated during the page's render() method
     *
     * @param string
     */
    protected $output;

    /**
     * The page object currently being dispatched.
     *
     * @param \Dewdrop\Admin\Page\PageAbstract
     */
    protected $page;

    /**
     * The response helper provided to the page's process() method
     * for short-circuiting of the dispatch logic.  Will only be
     * present if process() is called.
     *
     * @param \Dewdrop\Admin\ResponseHelper\Standard
     */
    protected $helper;

    /**
     * Whether the process() method was called (i.e. the page's
     * shouldProcess() method returned true.
     *
     * @var boolean
     */
    protected $wasProcessed;

    /**
     * Set the output generated during the request.
     *
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Return the generated output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set the page object generating the response so it can be interacted
     * with during testing.
     *
     * @param PageAbstract $page
     * @return \Dewdrop\Admin\Response\ResponseInterface
     */
    public function setPage(PageAbstract $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get the dispatched page object (usually only for testing)
     *
     * @return \Dewdrop\Admin\Page\PageAbstract
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set whether the page's process method was called.
     *
     * @param boolean $wasProcessed
     * @return \Dewdrop\Admin\Response\ResponseInterface
     */
    public function setWasProcessed($wasProcessed)
    {
        $this->wasProcessed = $wasProcessed;

        return $this;
    }

    /**
     * Set the response helper associated with this response.
     *
     * @param ResponseHelper $helper
     * @return \Dewdrop\Admin\Response\ResponseInterface
     */
    public function setHelper(ResponseHelper $helper)
    {
        $this->helper = $helper;

        return $this;
    }

    /**
     * Whether the component managing the page dispatch process should
     * render the generated output to the client.
     *
     * @return boolean
     */
    public function shouldRenderOutput()
    {
        return true;
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
        $this->helper->execute();
    }
}
