<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;
use Dewdrop\Pimple;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\File\MimeType;
use Zend\Validator\File\UploadFile;

class Import extends PageAbstract
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

    public function process(ResponseHelper $helper)
    {
        if ($this->request->isPost()) {
            $inputFilter = $this->buildInputFilter();

            $inputFilter->setData($_FILES);

            if ($inputFilter->isValid()) {
            } else {
                var_dump($inputFilter->getMessages());
                exit;
            }
        }
    }

    public function render()
    {
        $this->getView()->assign(
            [
                'title'         => $this->getTitle(),
                'singularTitle' => $this->getSingularTitle()
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

    private function buildInputFilter()
    {
        $inputFilter = new InputFilter();

        $input = new Input('file');
        $inputFilter->add($input);

        $mimeType = new MimeType();

        $mimeType->setMimeType(
            [
                'text/csv',
                'application/vmd.ms-excel',
                'application/vnd.ms-office',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]
        );

        $input->getValidatorChain()
            ->attach(new UploadFile())
            ->attach($mimeType);

        return $inputFilter;
    }
}
