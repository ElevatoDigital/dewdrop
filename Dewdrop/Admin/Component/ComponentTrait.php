<?php

namespace Dewdrop\Admin\Component;

use Dewdrop\Admin\Env\EnvInterface;
use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Admin\PageFactory\PageFactoryInterface;
use Dewdrop\Admin\Permissions;
use Dewdrop\Admin\Response;
use Dewdrop\Exception;
use Dewdrop\Inflector;
use ReflectionClass;

trait ComponentTrait
{
    /**
     * Whether the admin environment should wrap the page environment with the
     * layout (i.e. admin shell chrome.
     *
     * @var bool
     */
    protected $shouldRenderLayout = true;

    /**
     * The path to the component class.
     *
     * @var string
     */
    protected $path;

    /**
     * The admin environment.  Used to do anything environment (i.e. WP or Silex)
     * specific, like perform redirects.
     *
     * @var EnvInterface
     */
    protected $env;

    /**
     * The page factories registered with this component to provide page objects.
     *
     * @var array
     */
    private $pageFactories = array();

    /**
     * A page factory to allow overriding of page classes that come from other
     * factories.
     *
     * @var \Dewdrop\Admin\PageFactory\Custom
     */
    private $customPageFactory;

    /**
     * Callbacks registered with this component via onPageDispatch().
     *
     * @var array
     */
    private $pageDispatchCallbacks = [];

    /**
     * The permissions for this component.
     *
     * @var Permissions
     */
    private $permissions;

    /**
     * Get the path to this component's class.
     *
     * @return string
     */
    public function getPath()
    {
        if (!$this->path) {
            // Component metadata retrieved via reflection
            $reflectionClass = new ReflectionClass($this);
            $this->path = dirname($reflectionClass->getFileName());
        }

        return $this->path;
    }

    /**
     * Get a fully-qualified name for the component that can be used when
     * referencing the component in externals systems like the DB.
     *
     * @return string
     */
    public function getFullyQualifiedName()
    {
        return '/application/admin/' . $this->getName();
    }

    /**
     * Get an identifier that can be used when storing this component's
     * listing query string parameters in the session.  We store the params
     * so that we can redirect while maintaining filter and pagination
     * state.
     *
     * @return string
     */
    public function getListingQueryParamsSessionName()
    {
        return rtrim($this->getFullyQualifiedName(), '/') . '/listing-query-params';
    }

    /**
     * Get the Permissions object for this component.
     *
     * @return Permissions
     */
    public function getPermissions()
    {
        if (!$this->permissions) {
            $this->permissions = new Permissions($this);
        }

        return $this->permissions;
    }

    /**
     * Get all the page factories associated with this component.
     *
     * @return array
     */
    public function getPageFactories()
    {
        return $this->pageFactories;
    }

    /**
     * Get the custom page factory for this component.
     *
     * @return \Dewdrop\Admin\PageFactory\Custom
     */
    public function getCustomPageFactory()
    {
        return $this->customPageFactory;
    }

    /**
     * Add a new page factory to this component.
     *
     * @param PageFactoryInterface $pageFactory
     * @return $this
     */
    public function addPageFactory(PageFactoryInterface $pageFactory)
    {
        $this->pageFactories[] = $pageFactory;

        return $this;
    }

    /**
     * Iterate over the page factories, attempting to create a page object for
     * the supplied name.  If no page factory can handle the page name, that's
     * a 404.
     *
     * @param string $name
     * @return \Dewdrop\Admin\Page\PageAbstract|false
     * @throws Exception
     */
    public function createPageObject($name)
    {
        $page = false;

        /* @var $factory PageFactoryInterface */
        foreach ($this->getPageFactories() as $factory) {
            $page = $factory->createPage($name);

            if ($page) {
                $page->setName($name);
                break;
            }
        }

        if (!$page) {
            throw new Exception('Could not find page');
        }

        return $page;
    }

    /**
     * Register a callback to run when the page matching the supplied name
     * is dispatched on this component.  Your callback will receive the new
     * page object as its first argument.
     *
     * @param $pageName
     * @param callable $callback
     * @return $this
     */
    public function onPageDispatch($pageName, callable $callback)
    {
        if (!array_key_exists($pageName, $this->pageDispatchCallbacks)) {
            $this->pageDispatchCallbacks[$pageName] = [];
        }

        $this->pageDispatchCallbacks[$pageName][] = $callback;

        return $this;
    }

    /**
     * Dispatch the routed page, working through its init(), process() and
     * render() methods.
     *
     * Each abstract page class can specify whether the process() method
     * should actually be run by implementing a shouldProcess() method.
     * The page's init() method is guaranteed to always be called.  If
     * after calling render there is no output, the component will attempt
     * to render the page's default view script automatically.
     *
     * @param mixed $page
     * @param Response $response
     * @return mixed
     */
    public function dispatchPage($page = null, Response $response = null)
    {
        $this->active = true;

        if (!$this->getPermissions()->can('access')) {
            return $this->env->redirect('/admin/');
        }

        if (is_string($page)) {
            $page = $this->createPageObject($page);
        }

        $this->executePageDispatchCallbacks($page);

        if (null === $response) {
            $response = new Response($page, array($this->env, 'redirect'));
        }

        $page->init();

        if ($page->shouldProcess()) {
            $responseHelper = $page->createResponseHelper(array($this->env, 'redirect'));

            $page->process($responseHelper);

            $response
                ->setWasProcessed(true)
                ->setHelper($responseHelper);

            $result = $response->executeQueuedActions();

            if ($result) {
                return $result;
            }
        }

        ob_start();

        $output = $page->render();

        if (is_array($output)) {
            $this->renderJsonResponse($output);
        } elseif (!$output) {
            // Capture output generated during render rather than returned
            $output = ob_get_clean();
        }

        // Automatically render view if no output is generated
        if (!$output) {
            $output = $page->renderView();
        }

        if (!$this->shouldRenderLayout) {
            return $output;
        } else {
            return $this->env
                ->setActiveComponent($this)
                ->renderLayout(
                    $output,
                    $page->getView()->headScript(),
                    $page->getView()->headLink()
                );
        }
    }

    /**
     * Set whether the admin environment should wrap the page's output with
     * the layout (the admin shell chrome).
     *
     * @param boolean $shouldRenderLayout
     * @return $this
     */
    public function setShouldRenderLayout($shouldRenderLayout)
    {
        $this->shouldRenderLayout = $shouldRenderLayout;

        return $this;
    }

    /**
     * Check to see whether the page output should be wrapped by the layout.
     *
     * @return bool
     */
    public function shouldRenderLayout()
    {
        return $this->shouldRenderLayout;
    }

    /**
     * Get WP slug for this component.
     *
     * We use the component name, with namespace back slashes replaced with
     * URL-friendly front slashes, as the slug.
     *
     * @return string
     */
    public function getSlug()
    {
        $fullClass = str_replace('\\', '/', get_class($this));
        $segments  = explode('/', $fullClass);
        $nameIndex = count($segments) - 2;

        return $segments[$nameIndex];
    }

    /**
     * Render the supplied output as a JSON response.  This method is mostly
     * in place to allow mocking (and thus dodging the exit statement) during
     * testing.
     *
     * @param array $output
     * @return void
     */
    protected function renderJsonResponse(array $output)
    {
        header('Content-Type: application/json');
        echo json_encode($output);
        exit;
    }

    /**
     * Run any page dispatch callbacks assigned to the provided page.  We support
     * both the WP and hyphen-separated versions of the page names in this method
     * to make it easier to reuse page dispatch code across different environments.
     *
     * @param PageAbstract $page
     * @return $this
     */
    private function executePageDispatchCallbacks(PageAbstract $page)
    {
        /* @var $inflector Inflector */
        $inflector = ($this->hasPimpleResource('inflector') ? $this->getPimpleResource('inflector') : new Inflector());
        $names     = [$page->getName(), $inflector->hyphenize($page->getName())];

        foreach ($names as $name) {
            if (array_key_exists($name, $this->pageDispatchCallbacks)) {
                foreach ($this->pageDispatchCallbacks[$name] as $callback) {
                    call_user_func($callback, $page);
                }
                break;
            }
        }

        return $this;
    }
}
