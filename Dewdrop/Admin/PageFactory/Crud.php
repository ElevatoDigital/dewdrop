<?php

namespace Dewdrop\Admin\PageFactory;

use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\Component\Silex as SilexComponent;
use Dewdrop\Fields;
use Dewdrop\Fields\Listing;
use Dewdrop\Inflector;

class Crud
{
    private $component;

    private $inflector;

    private $path;

    private $classPrefix;

    public function __construct(CrudInterface $component, $path = null, $classPrefix = null)
    {
        $this->component = $component;
        $this->inflector = $component->getInflector();

        if (null === $path) {
            if ($component instanceof SilexComponent) {
                $this->path        = realpath(__DIR__ . '/../Page/Stock/Silex');
                $this->classPrefix = '\Dewdrop\Admin\Page\Stock\Silex\\';
            }
        }
    }

    public function createPage($name)
    {
        $inflectedName = $this->inflector->camelize($name);
        $pagePath      = $this->path . '/' . $inflectedName . '.php';

        if (file_exists($pagePath)) {
            $pageClass = $this->classPrefix . $inflectedName;

            return new $pageClass(
                $this->component,
                $this->component->getRequest(),
                $this->path . '/view-scripts'
            );
        }
    }
}
