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
        // @todo Ensure filesystem path is in document root.

        $path = str_replace(
            $_SERVER['DOCUMENT_ROOT'],
            '',
            $this->filesystemPath
        );

        return sprintf(
            '//%s/%s',
            $_SERVER['HTTP_HOST'],
            ltrim($path, '/')
        );
    }

    public function getFilesystemPath()
    {
        return $this->filesystemPath;
    }
}
