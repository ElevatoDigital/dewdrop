<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component;

use Dewdrop\ActivityLog\Handler\HandlerInterface as ActivityLogHandlerInterface;
use Dewdrop\ActivityLog\Handler\NullHandler as ActivityLogNullHandler;
use Dewdrop\Admin\Env\EnvInterface;
use Dewdrop\Admin\PageFactory\Files as PageFilesFactory;
use Dewdrop\Admin\PageFactory\PageFactoryInterface;
use Dewdrop\Exception;
use Dewdrop\Admin\Permissions;
use Dewdrop\Pimple as DewdropPimple;
use Pimple;

/**
 * This class enables you to define how your component should appear and wire
 * into the admin shell.  For example, the $title property determines what
 * your plugin will be labeled in the admin navigation and the addToSubmenu()
 * method will allow you to add submenu items for your component.
 */
abstract class ComponentAbstract implements ComponentInterface, ShellIntegrationInterface
{
    use ComponentTrait;

    /**
     * An array of submenu pages that have been added by calling addToSubmenu().
     * These are actually tied in by registerMenuPage() after the admin_menu
     * hook is triggered.
     *
     * @var array
     */
    protected $submenuPages = array();

    /**
     * The component name (as it would show up in the URL, for example).
     *
     * @var string
     */
    protected $name;

    /**
     * The title to display for this component in the admin navigation.
     *
     * @var string
     */
    private $title;

    /**
     * The icon that should be used for this component's navigation link.
     *
     * @var string
     */
    private $icon;

    /**
     * The position this item should take in the admin navigation menu.
     *
     * To see the current menu positions, you can var_dump($_GLOBALS['menu']).
     * Note that is you pick an already used menu position, you will essentially
     * hide that item.  For example, if you set your menu position to "5",
     * which is the default value for "Posts", your component will show up there
     * instead of "Posts", rather than just pushing "Posts" further down like
     * you might expect.  You can leave $menuPosition blank if you'd simply
     * like your component to be appended to the end of the menu.
     *
     * @var integer
     */
    private $menuPosition = null;

    /**
     * Content that should be displayed in a "badge" alongside this component's
     * menu item.
     *
     * @var string
     */
    private $badgeContent = null;

    /**
     * The Pimple object used to supply dependencies to the admin component.
     * Basically used as a service locator in this context, which makes testability
     * a bit trickier in the context of the component class itself.  However, it
     * makes using component classes in other contexts (e.g. testing your page
     * classes) much easier.
     *
     * @var Pimple
     */
    protected $pimple;

    /**
     * The admin environment.  Used to do anything environment (i.e. WP or Silex)
     * specific, like perform redirects.
     *
     * @var EnvInterface
     */
    protected $env;

    /**
     * The path to the component class.
     *
     * @var string
     */
    protected $path;

    /**
     * @var ActivityLogHandlerInterface
     */
    private $activityLogHandler;

    /**
     * Whether this component is active (currently in charge of dispatching a
     * page/response).
     *
     * @var boolean
     */
    private $active = false;

    /**
     * Create a component instance using the DB adapter creating by the Wiring
     * class.
     *
     * @param Pimple $pimple
     * @param string $componentName
     */
    public function __construct(Pimple $pimple = null)
    {
        $this->pimple = ($pimple ?: DewdropPimple::getInstance());
        $this->env    = $this->getPimpleResource('admin');

        $this->activityLogHandler = new ActivityLogNullHandler();

        // Setup the default page factory, which looks for files in the component's folder
        $this->addPageFactory(new PageFilesFactory($this));

        if (isset($pimple['custom-page-factory'])) {
            $this->customPageFactory = $this->pimple['custom-page-factory'];
            $this->customPageFactory->setComponent($this);
            $this->addPageFactory($this->customPageFactory);
        }

        $this->init();

        $this->checkRequiredProperties();
    }

    /**
     * Your component sub-class will use the init method to set basic component
     * information like the title and the icon.
     */
    abstract public function init();

    /**
     * This method will be called before any admin page is dispatched.
     */
    public function preDispatch()
    {

    }

    /**
     * Check to see if the supplied resource is present in the component's Pimple
     * or in the Dewdrop Pimple.
     *
     * @param string $name
     * @return bool
     */
    public function hasPimpleResource($name)
    {
        return isset($this->pimple[$name]) || DewdropPimple::hasResource($name);
    }

    /**
     * Get the named Pimple resource from the component's local container or the
     * Dewdrop container.
     *
     * @param $name
     * @return mixed
     */
    public function getPimpleResource($name)
    {
        if (isset($this->pimple[$name])) {
            return $this->pimple[$name];
        } else {
            return DewdropPimple::getResource($name);
        }
    }

    /**
     * Get the Pimple container used by this component.
     *
     * @return Pimple
     */
    public function getPimple()
    {
        return $this->pimple;
    }

    /**
     * Get a Inflector object.
     *
     * @deprecated
     * @return \Dewdrop\Inflector
     */
    public function getInflector()
    {
        return $this->getPimpleResource('inflector');
    }

    /**
     * Get a Paths object.
     *
     * @deprecated
     * @return \Dewdrop\Paths
     */
    public function getPaths()
    {
        return $this->getPimpleResource('paths');
    }

    /**
     * Get a Request object.
     *
     * @return \Dewdrop\Request
     */
    public function getRequest()
    {
        return $this->getPimpleResource('dewdrop-request');
    }

    /**
     * Get the DB adapter associated with this component.  This is used
     * frequently by page classes to get access to the DB connection.
     *
     * @return \Dewdrop\Db\Adapter
     */
    public function getDb()
    {
        return $this->getPimpleResource('db');
    }

    /**
     * @param ActivityLogHandlerInterface $activityLogHandler
     * @return $this
     */
    public function setActivityLogHandler(ActivityLogHandlerInterface $activityLogHandler)
    {
        $this->activityLogHandler = $activityLogHandler;

        return $this;
    }

    /**
     * @return ActivityLogHandlerInterface
     */
    public function getActivityLogHandler()
    {
        return $this->activityLogHandler;
    }

    /**
     * Check to see if this component is active.
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
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
            throw new Exception("Could not find page by name \"{$name}\"");
        }

        return $page;
    }

    /**
     * Request that the admin Env assemble a URL that will work to get to the
     * specified page.
     *
     * @param string $page
     * @param array $params
     * @returns string
     */
    public function url($page, array $params = array())
    {
        return $this->env->url($this, $page, $params);
    }

    /**
     * Add a link to the submenu for this component.
     *
     * @param string $title
     * @param string $page
     * @param array $params
     * @return ComponentAbstract
     */
    public function addToSubmenu($title, $page, $params = array())
    {
        $this->submenuPages[] = array(
            'title' => $title,
            'route' => ucfirst($page),
            'params' => $params,
            'url' => $this->url($page, $params)
        );

        return $this;
    }

    /**
     * Add a divider to the component's submenu.
     *
     * @return $this
     */
    public function addSubmenuDivider()
    {
        $this->submenuPages[] = array(
            'isDivider' => true
        );

        return $this;
    }

    /**
     * Get all sub-menu pages that have been added to this component.
     *
     * @return array
     */
    public function getSubmenuPages()
    {
        return $this->submenuPages;
    }

    /**
     * Get the name of the component (i.e. the string that would represent it
     * in the URL.
     *
     * @return string
     */
    public function getName()
    {
        if (!$this->name) {
            $this->name = $this->env->inflectComponentName(basename($this->getPath()));

        }

        return $this->name;
    }

    /**
     * Allow overriding of the admin environment after instantiation.
     *
     * @param EnvInterface $env
     * @return $this
     */
    public function setEnv(EnvInterface $env)
    {
        $this->env = $env;
        return $this;
    }

    /**
     * Set the title of this component as it should be displayed in the
     * admin menu.
     *
     * @param string $title
     * @return ComponentAbstract
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the title of the component that would be displayed in the admin menu.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the badge content that should be displayed alongside this component's
     * navigation.
     *
     * @param string $badgeContent
     * @return $this
     */
    public function setBadgeContent($badgeContent)
    {
        $this->badgeContent = $badgeContent;

        return $this;
    }

    /**
     * Get the badge content assigned to this component.
     *
     * @return string
     */
    public function getBadgeContent()
    {
        return $this->badgeContent;
    }

    /**
     * Set the icon that should be used in this component's admin menu
     * entry.
     *
     * @param string $icon
     * @return ComponentAbstract
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get the icon that should be displayed for the component in the
     * admin menu.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set the position of the component's menu entry
     *
     * @see $menuPosition
     * @param integer $menuPosition
     * @return ComponentAbstract
     */
    public function setMenuPosition($menuPosition)
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    /**
     * Get the position at which this component should be rendered in the
     * admin shell nav menu.
     *
     * @return int
     */
    public function getMenuPosition()
    {
        return $this->menuPosition;
    }

    /**
     * Make sure the title property was set in the component's init()
     * method.
     *
     * @throws Exception
     */
    protected function checkRequiredProperties()
    {
        if (!$this->title) {
            throw new Exception('Component title is required');
        }
    }
}
