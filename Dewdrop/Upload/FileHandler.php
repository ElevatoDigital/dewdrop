<?php

namespace Dewdrop\Upload;

use Dewdrop\SetOptionsTrait;
use Zend\Filter\FilterChain;
use Zend\Validator\File\MimeType as MimeTypeValidator;
use Zend\Validator\File\UploadFile as UploadFileValidator;
use Zend\Validator\ValidatorChain;

class FileHandler
{
    use SetOptionsTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $uploadPath;

    /**
     * @var array
     */
    private $allowedMimeTypes = [];

    /**
     * @var ValidatorChain
     */
    private $validatorChain;

    /**
     * @var FilterChain
     */
    private $filterChain;

    public function __construct($name, array $options = array())
    {
        $this
            ->setName($name)
            ->setOptions($options);
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $uploadPath
     * @return $this
     */
    public function setUploadPath($uploadPath)
    {
        $this->uploadPath = $uploadPath;

        return $this;
    }

    public function getUploadPath()
    {
        return $this->uploadPath;
    }

    public function setAllowedMimeTypes(array $allowedMimeTypes)
    {
        $this->allowedMimeTypes = $allowedMimeTypes;

        return $this;
    }

    public function getAllowedMimeTypes()
    {
        return $this->allowedMimeTypes;
    }

    public function allowMimeType($mimeType)
    {
        $this->allowedMimeTypes[] = $mimeType;

        return $this;
    }

    public function resetValidatorChain()
    {
        $this->validatorChain = null;

        return $this;
    }

    public function getValidatorChain()
    {
        if (!$this->validatorChain) {
            $this->validatorChain = new ValidatorChain();

            $uploadFileValidator = new UploadFileValidator();
            $this->validatorChain->attach($uploadFileValidator);

            if(!empty($this->allowedMimeTypes)) {
                $mimeTypeValidator = new MimeTypeValidator();
                $mimeTypeValidator->addMimeType($this->allowedMimeTypes);
                $this->validatorChain->attach($mimeTypeValidator);
            }
        }

        return $this->validatorChain;
    }

    public function getFilterChain()
    {
        if (!$this->filterChain) {
            $this->filterChain = new FilterChain();
        }

        return $this->filterChain;
    }

    public function moveUploadedFile(array $fileInfo)
    {
        $pathInfo = pathinfo($fileInfo['name']);

        $path = sprintf(
            '%s/%s',
            rtrim($this->uploadPath, '/'),
            substr(hash('sha256', file_get_contents($fileInfo['tmp_name'])), 0, 16)
        );

        if (!is_dir($path)) {
            mkdir($path, 0777);
        }

        $destination = sprintf(
            '%s/%s.%s',
            $path,
            $pathInfo['filename'],
            $pathInfo['extension']
        );

        if (!move_uploaded_file($fileInfo['tmp_name'], $destination)) {
            return false;
        } else {
            $destination = $this->getFilterChain()->filter($destination);

            return new UploadedFile($destination);
        }
    }
}
