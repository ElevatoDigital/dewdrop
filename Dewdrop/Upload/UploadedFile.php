<?php

namespace Dewdrop\Upload;

class UploadedFile
{
    /**
     * @var string
     */
    private $filesystemPath;

    public function __construct($filesystemPath)
    {
        $this->filesystemPath = $filesystemPath;
    }

    public function getUrl()
    {
        return $this->convertFilesystemPathToUrl($this->filesystemPath);
    }

    public function getFilesystemPath()
    {
        return $this->filesystemPath;
    }

    public function getValue()
    {
        return $this->getUrl();
    }

    public function getThumbnail()
    {
        return null;
    }

    protected function convertFilesystemPathToUrl($filesystemPath)
    {
        $path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filesystemPath);

        return sprintf(
            '//%s/%s',
            $_SERVER['HTTP_HOST'],
            ltrim($path, '/')
        );
    }
}
