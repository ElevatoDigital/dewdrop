<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;
use Dewdrop\Exception;
use Dewdrop\Import\DbGateway;
use Dewdrop\Import\File as ImportFile;
use Dewdrop\Import\InputFilterFactory as ImportInputFilterFactory;
use Dewdrop\Pimple;

class Import extends StockPageAbstract
{
    /**
     * @var ComponentAbstract|CrudInterface
     */
    protected $component;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $singularTitle;

    /**
     * @var string
     */
    private $uploadPath;

    /**
     * @var array
     */
    private $validationMessages = [];

    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('import');
    }

    public function process(ResponseHelper $helper)
    {
        if ($this->request->isPost()) {
            $inputFilter = ImportInputFilterFactory::createInstance();

            $inputFilter->setData($_FILES);

            if (!$inputFilter->isValid() || !is_uploaded_file($_FILES['file']['tmp_name'])) {
                $this->validationMessages = $inputFilter->getMessages();
            } else {
                $file = ImportFile::fromUploadedFile(
                    $_FILES['file'],
                    $this->getUploadPath(),
                    $this->request->getPost('first_row_is_headers')
                );

                $gateway = new DbGateway();

                $id = $gateway->insert(
                    [
                        'component'            => $this->component->getFullyQualifiedName(),
                        'full_path'            => $file->getFullPath(),
                        'first_row_is_headers' => (int) $this->request->getPost('first_row_is_headers')
                    ]
                );

                $helper
                    ->setSuccessMessage('Import file successfully uploaded.')
                    ->redirectToAdminPage('import-map-fields', ['id' => $id]);
            }
        }
    }

    public function render()
    {
        $this->getView()->assign(
            [
                'title'              => $this->getTitle(),
                'singularTitle'      => $this->getSingularTitle(),
                'validationMessages' => $this->validationMessages
            ]
        );
    }

    public function getTitle()
    {
        if (!$this->title) {
            $this->title = $this->component->getTitle();
        }

        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getSingularTitle()
    {
        if (!$this->singularTitle) {
            $this->singularTitle = $this->component->getPrimaryModel()->getSingularTitle();
        }

        return $this->singularTitle;
    }

    public function setSingularTitle($singularTitle)
    {
        $this->singularTitle = $singularTitle;

        return $this;
    }

    public function getUploadPath()
    {
        if (!$this->uploadPath) {
            /* @var $paths \Dewdrop\Paths */
            $paths = Pimple::getResource('paths');
            $root  = $paths->getAppRoot();

            $this->uploadPath = $root . '/private-uploads/dewdrop-import/'
                . str_replace('/', '__', $this->component->getFullyQualifiedName());
        }

        return $this->uploadPath;
    }

    public function setUploadPath($uploadPath)
    {
        $this->uploadPath = $uploadPath;

        return $this;
    }
}
