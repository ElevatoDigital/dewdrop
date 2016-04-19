<?php

namespace Dewdrop\Admin\Component;

use Dewdrop\Admin\PageFactory\PageFactoryInterface;
use Dewdrop\Admin\Response;

interface ComponentInterface
{
    public function init();

    public function preDispatch();

    public function getPermissions();

    public function addPageFactory(PageFactoryInterface $pageFactory);

    public function getPageFactories();

    public function url($page, array $params = []);

    public function getFullyQualifiedName();

    public function getListingQueryParamsSessionName();

    public function onPageDispatch($pageName, callable $callback);

    public function dispatchPage($page = null, Response $response = null);

    public function hasPimpleResource($name);

    public function getPimpleResource($name);

    public function getName();

    public function getSlug();

    /**
     * Get the path to this component's class.
     *
     * @return string
     */
    public function getPath();

    /**
     * @return string
     */
    public function getTitle();
}
