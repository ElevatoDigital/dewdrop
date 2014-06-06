<?php

namespace Dewdrop\Admin;

use Dewdrop\View\View;
use DirectoryIterator;
use Silex\Application;

class Silex
{
    private $title = 'Admin';

    private $application;

    private $components = array();

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

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
    }

    public function registerAdminComponent($folder)
    {
        require_once $folder . '/Component.php';
        $componentName = basename($folder);
        $className     = '\Admin\\' . $this->application['inflector']->camelize($componentName) . '\Component';

        $component = new $className($this, $componentName);

        $component->register();

        $this->components[] = $component;
    }

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
