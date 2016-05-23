<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Upload\Exception as UploadException;
use Dewdrop\Upload\FileHandler;
use Dewdrop\Upload\UploadedFile;
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;

class Upload extends StockPageAbstract
{
    /**
     * @var array
     */
    private $fileHandlers = [];

    /**
     * @var mixed
     */
    private $error;

    /**
     * @var UploadedFile
     */
    private $uploadedFile;

    public function addFileHandler(FileHandler $fileHandler)
    {
        $this->fileHandlers[] = $fileHandler;

        return $this;
    }

    public function process(ResponseHelper $response)
    {
        if (!$this->request->isPost()) {
            $this->error = 'Upload request must be POST.';
            return;
        } else if (!count($_FILES)) {
            $this->error = 'No file uploads were present.  Check form encoding or upload_max_filesize.';
            return;
        } else {
            $handlerFound = null;

            /* @var $fileHandler FileHandler */
            foreach ($this->fileHandlers as $fileHandler) {
                foreach ($_FILES as $name => $fileInfo) {
                    if ($name === $fileHandler->getName()) {
                        if ($this->isValid($fileHandler, $fileInfo)) {
                            $this->processValidUpload($fileHandler, $fileInfo);
                        }

                        $handlerFound = true;
                        break;
                    }
                }
            }

            if (!$handlerFound) {
                $this->error = 'No file handler found for uploaded $_FILES.';
                return;
            }
        }
    }

    private function isValid(FileHandler $fileHandler, array $fileInfo)
    {
        $validatorChain = $fileHandler->getValidatorChain();

        if ($validatorChain->isValid($fileInfo)) {
            return true;
        } else {
            $this->error = $validatorChain->getMessages();
            return false;
        }
    }

    private function processValidUpload(FileHandler $fileHandler, array $fileInfo)
    {
        try {
            $this->uploadedFile = $fileHandler->moveUploadedFile($fileInfo);
        } catch (UploadException $e) {
            $this->error = 'Could not save the uploaded file.';
        }
    }

    public function render()
    {
        if ($this->error) {
            // Address inconsistency between ZF's getMessages() and other error messages
            if (!is_array($this->error)) {
                $this->error = [$this->error];
            }

            return ['result' => 'error', 'messages' => $this->error];
        } else {
            return [
                'result'    => 'success',
                'url'       => $this->uploadedFile->getUrl(),
                'value'     => $this->uploadedFile->getValue(),
                'thumbnail' => $this->uploadedFile->getThumbnail()
            ];
        }
    }
}
