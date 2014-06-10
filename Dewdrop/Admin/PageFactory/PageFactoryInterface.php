<?php

namespace Dewdrop\Admin\PageFactory;

interface PageFactoryInterface
{
    public function createPage($name);

    public function listAvailablePages();
}
