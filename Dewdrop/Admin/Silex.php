<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin;

use Dewdrop\View\View;
use DirectoryIterator;
use Silex\Application;

/**
 * This class is responsible for the shell-level admin functionality in Silex
 * applications.  It will wrap Component responses in an admin shell layout when
 * appropriate, assist in the finding and registering of admin components, etc.
 */
class Silex
{
    /**
     * The title for the admin area.  Typically your application's name, the
     * name of the client's company, etc.
     *
     * @var string
     */
    private $title = 'Admin';

    /**
     * The \Silex\Application object.
     *
     * @var Application
     */
    private $application;

    /**
     * The registered \Dewdrop\Admin\ComponentAbstract objects.
     *
     * @var array
     */
    private $components = array();

    /**
     * Provide a \Silex\Application object that can be used to retrieve
     * resources, register routes, etc.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Get the \Silex\Application used by the admin shell.
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the title of this admin area.  Will be displayed in the main
     * nav, etc.
     *
     * @param string $title
     * @return Silex
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Look for and register all admin components in the given path.  If
     * no path is provided, the \Dewdrop\Paths->getAdmin() method will be
     * used to find the default admin path for the application.
     *
     * @param string $path
     * @return Silex
     */
    public function registerComponentsInPath($path = null)
    {
        if (null === $path) {
            $path = $this->application['paths']->getAdmin();
        }

        $adminFolders     = new DirectoryIterator($path);
        $componentFolders = array();

        foreach ($adminFolders as $folder) {
            if (0 === strpos($folder, '.')) {
                continue;
            }

            if (is_dir($path . '/' . $folder)) {
                $componentFolders[] = $path . '/'. $folder;
            }
        }

        foreach ($componentFolders as $folder) {
            $this->registerAdminComponent($folder);
        }

        return $this;
    }

    /**
     * Register the single admin component located in the supplied path.  This
     * can be useful if you want to register individual components that are
     * outside your normal folder for admin components.  For example, if you've
     * got some reuseable admin components in a library, or Dewdrop itself, you
     * could register them with this method.
     *
     * @param string $folder
     * @return Silex
     */
    public function registerAdminComponent($folder)
    {
        require_once $folder . '/Component.php';
        $componentName = basename($folder);
        $className     = '\Admin\\' . $this->application['inflector']->camelize($componentName) . '\Component';

        $component = new $className($this->application, $componentName);

        $component->register();

        $this->components[] = $component;

        return $this;
    }

    /**
     * Render the admin shell's layout, placing the supplied content in the
     * appropriate area of the HTML.
     *
     * @param string $content
     * @return string
     */
    public function renderLayout($content)
    {
        $view = new View();
        $view->setScriptPath(__DIR__ . '/view-scripts');

        $view
            ->assign('title', $this->title)
            ->assign('components', $this->components)
            ->assign('content', $content);

        return $view->render('layout.phtml');
    }
}
