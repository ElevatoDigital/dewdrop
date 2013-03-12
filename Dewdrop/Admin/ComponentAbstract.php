<?php

namespace Dewdrop\Admin;

abstract class ComponentAbstract
{
    private $title;

    private $icon;

    private $menuPosition;

    private $wiring;

    public function __construct($db, $wiring)
    {
        $this->db     = $db;
        $this->wiring = $wiring;

        $this->init();

        $this->checkRequiredProperties();
    }

    abstract public function init();

    public function register()
    {
        add_action('admin_menu', array($this, 'registerMenuPage'));
    }

    public function getModel($name)
    {
        return $this->wiring->getModel($name);
    }

    public function route()
    {
        $pageKey   = basename(isset($_GET['route']) ? $_GET['route'] : 'Index');
        $pageFile  = dirname(dirname(dirname(__DIR__))) . '/admin/fruits/' . $pageKey . '.php';
        $className = '\Admin\Fruits\\' . $pageKey;

        require_once $pageFile;
        $page = new $className($this, $pageFile);

        echo $page->render();
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
}
