<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\ResponseHelper;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Pimple;
use Dewdrop\Session;

/**
 * The response helper object gets injected into a page's process() method
 * and allows the page author to perform redirects or set status messages.
 *
 * The response helper does not perform any actions until its execute()
 * method is called -- generally by the component object that dispatched
 * the page in the first place.  This delayed execution of the actions
 * defined during your page's process method makes it easier to write
 * unit tests for your page.
 *
 * For example, after writing a test that supplies valid data to a form,
 * you could test to see if the page would redirect like you expect it to
 * by calling this object's hasRedirectUrl() method and neglecting to
 * call its execute() method at all.
 */
class Standard
{
    /**
     * The page that created this helper.  The page object is used for
     * creating URLs, etc.
     *
     * @var \Dewdrop\Admin\Page\PageAbstract
     */
    private $page;

    /**
     * A success message that you would like to be displayed to the user
     * on the next page load.  (The message is typically displayed by
     * the WpAdminNotice view helper.)
     *
     * @var string
     */
    private $successMessage;

    /**
     * The URL you'd like to redirect to after the process() method.
     *
     * @var string
     */
    private $redirectUrl;

    /**
     * Callbacks to run as part of executing the response
     *
     * @var array
     */
    private $callbacks = array();

    /**
     * A callable that can be used to execute the queued redirect.
     * Redirecting varies quite a bit between WP and Silex.  In WP, it's just
     * a normal function.  In Silex, the redirect method returns a response,
     * which must then be returned from the controller.
     *
     * @var callable
     */
    private $redirector;

    /**
     * A Dewdrop\Session object that can be used to store success messages
     * across redirects.
     *
     * @var Session
     */
    private $session;

    /**
     * Create a new ResponseHelper, generally kicked off by a page class in
     * its createResponseHelper() method.
     *
     * @param PageAbstract $page
     * @param callable $redirector
     * @param Session $session
     */
    public function __construct(PageAbstract $page, callable $redirector, Session $session = null)
    {
        $this->page       = $page;
        $this->redirector = $redirector;
        $this->session    = ($session ?: new Session());
    }

    /**
     * Set a success message you'd like displayed on the next request.
     *
     * @see $successMessage
     * @param $message string
     * @return \Dewdrop\Admin\ResponseHelper\Standard
     */
    public function setSuccessMessage($message)
    {
        $this->successMessage = $message;

        return $this;
    }

    /**
     * Set the redirect URL to a page in the current component.
     *
     * The page parameter should be supplied with capitalization matching
     * the file and class name (e.g. "Index", not "index").
     *
     * @param string $page
     * @param array $params
     * @return \Dewdrop\Admin\ResponseHelper\Standard
     */
    public function redirectToAdminPage($page, array $params = array())
    {
        $this->redirectUrl = $this->page->url($page, $params);

        return $this;
    }

    /**
     * Redirect to an arbitrary URL rather than a page in the same admin component.
     *
     * @param string $url
     * @return $this
     */
    public function redirectToUrl($url)
    {
        $this->redirectUrl = $url;

        return $this;
    }

    /**
     * Perform any actions that have been setup by the page's process()
     * method.  In a testing environment, you'd likely skip this method to
     * avoid "exit" following redirects, etc.
     *
     * @return bool
     */
    public function execute()
    {
        $redirectResult = false;

        $this->executeCallbacks();

        // Don't execute header modifying actions when on CLI
        if ('cli' !== php_sapi_name()) {
            $this->executeSuccessMessage();

            $redirectResult = $this->executeRedirect();
        }

        return $redirectResult;
    }

    /**
     * Run any queued callbacks.  Normally, this will just be called by the
     * execute() method, but sometimes in testing, you may want to selectively
     * execute portions of the response payload.
     *
     * @return \Dewdrop\Admin\ResponseHelper\Standard
     */
    public function executeCallbacks()
    {
        foreach ($this->callbacks as $callback) {
            call_user_func($callback);
        }

        return $this;
    }

    /**
     * Set the success message, if one has been assigned.
     *
     * @return \Dewdrop\Admin\ResponseHelper\Standard
     */
    public function executeSuccessMessage()
    {
        if ($this->successMessage) {
            $this->session['successMessage'] = $this->successMessage;
        }

        return $this;
    }

    /**
     * Execute the assigned redirect.
     *
     * @return \Dewdrop\Admin\ResponseHelper\Standard
     */
    public function executeRedirect()
    {
        if ($this->redirectUrl) {
            return call_user_func($this->redirector, $this->redirectUrl);
        }

        return false;
    }

    /**
     * Check whether a redirect URL has been set.  Can be useful in testing to
     * see if validation succeeded and a redirect was set.
     *
     * @return boolean
     */
    public function hasRedirectUrl()
    {
        return null !== $this->redirectUrl;
    }

    /**
     * Check whether a success message has been set.  Can be useful in testing to
     * see if validation succeeded and a success message was added.
     *
     * @return boolean
     */
    public function hasSuccessMessage()
    {
        return null !== $this->successMessage;
    }

    /**
     * Check to see if the given callback label will run if the response is
     * executed.
     *
     * @param string $callbackLabel
     * @return boolean
     */
    public function willRun($callbackLabel)
    {
        return array_key_exists($callbackLabel, $this->callbacks);
    }

    /**
     * Check to see if the executing the queued actions will short circuit the
     * response.
     *
     * @return boolean
     */
    public function willShortCircuitResponse()
    {
        return $this->hasRedirectUrl();
    }

    /**
     * Schedule a callback to run while executing the response.
     *
     * If the $callback parameter is null, then the $label parameter
     * will be used for both the label and the callback and the callback
     * will be assumed to be a method on the current page.
     *
     * @param string $label
     * @param mixed $callback
     * @return \Dewdrop\Admin\ResponseHelper\Standard
     */
    public function run($label, $callback = null)
    {
        if (null === $callback) {
            $callback = $label;
        }

        if (is_string($callback)) {
            $callback = array($this->page, $callback);
        }

        $this->callbacks[$label] = $callback;

        return $this;
    }
}
