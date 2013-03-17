<?php

namespace Dewdrop\Admin;

use ReflectionClass;

abstract class ComponentAbstract
{
    private $title;

    private $icon;

    private $menuPosition;

    public function __construct(\Dewdrop\Db\Adapter $db)
    {
        $this->db = $db;

        $this->init();

        $this->checkRequiredProperties();
    }

    abstract public function init();

    public function register()
    {
        add_action('admin_menu', array($this, 'registerMenuPage'));
    }

    public function route()
    {
        $reflectedClass = new ReflectionClass($this);

        $pageKey   = basename(isset($_GET['route']) ? $_GET['route'] : 'Index');
        $pageFile  = dirname($reflectedClass->getFileName()) . '/' . $pageKey . '.php';
        $className = $reflectedClass->getNamespaceName() . '\\' . $pageKey;

        require_once $pageFile;
        $page = new $className($this, $pageFile);

        $this->dispatchPage($page);
    }

    public function registerMenuPage()
    {
        add_menu_page(
            $this->title,
            $this->title,
            'add_users',
            get_class($this),
            array($this, 'route'),
            $this->icon,
            $this->menuPosition
        );
    }

    public function getDb()
    {
        return $this->db;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    public function setMenuPosition($menuPosition)
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    protected function checkRequiredProperties()
    {
        if (!$this->title) {
            throw new \Dewdrop\Exception('Component title is required');
        }
    }

    protected function dispatchPage($page)
    {
        $page->init();

        if ($page->shouldProcess()) {
            $page->process();
        }

        $page->render();

        // Automatically render view if no output is generated
        if (!$output) {
            $page->renderView();
        }
    }
}
