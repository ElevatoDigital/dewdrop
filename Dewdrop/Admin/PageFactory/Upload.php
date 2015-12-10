<?php

namespace Dewdrop\Admin\PageFactory;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Page\Stock\Upload as UploadPage;
use Dewdrop\Upload\FileHandler;

class Upload implements PageFactoryInterface
{
    /**
     * @var ComponentAbstract
     */
    private $component;

    /**
     * @var array
     */
    private $fileHandlers = [];

    public function __construct(ComponentAbstract $component)
    {
        $this->component = $component;
    }

    public function addFileHandler(FileHandler $fileHandler)
    {
        $this->fileHandlers[] = $fileHandler;

        return $this;
    }

    public function createPage($name)
    {
        if ('upload' === $name) {
            $page = new UploadPage($this->component, $this->component->getRequest());

            foreach ($this->fileHandlers as $fileHandler) {
                $page->addFileHandler($fileHandler);
            }

            return $page;
        }

        return false;
    }

    public function listAvailablePages()
    {
        $pages = [];
        $pages[] = new Page('upload', __DIR__ . '/../Page/Stock/Upload.php', '\Dewdrop\Admin\Page\Stock\Upload');
        return $pages;
    }
}
