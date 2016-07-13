<?php

namespace Dewdrop\Admin\Component;

interface ShellIntegrationInterface
{
    /**
     * @return boolean
     */
    public function isActive();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $badgeContent
     * @return $this
     */
    public function setBadgeContent($badgeContent);

    /**
     * @return string
     */
    public function getBadgeContent();

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon);

    /**
     * @return string
     */
    public function getIcon();

    /**
     * @param integer $menuPosition
     * @return $this
     */
    public function setMenuPosition($menuPosition);

    /**
     * @return integer
     */
    public function getMenuPosition();
}
