<?php

namespace Dewdrop\Import;

use Dewdrop\Exception;
use PHPExcel_IOFactory;

class File
{
    /**
     * @var array
     */
    private $headers;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $fullPath;

    /**
     * @var boolean
     */
    private $firstRowIsHeaders;

    /**
     * @var \PHPExcel
     */
    private $phpExcel;

    public function __construct($fullPath, $firstRowIsHeaders)
    {
        $this->fullPath          = $fullPath;
        $this->firstRowIsHeaders = (boolean) $firstRowIsHeaders;
    }

    public function getFullPath()
    {
        return $this->fullPath;
    }

    public static function fromUploadedFile(array $fileInfo, $uploadPath, $firstRowIsHeaders)
    {
        if (!file_exists($uploadPath) || !is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $pathInfo = pathinfo($fileInfo['name']);
        $filename = hash('sha256', file_get_contents($fileInfo['tmp_name'])) . '.' . $pathInfo['extension'];
        $fullPath = $uploadPath . '/' . $filename;

        if (!move_uploaded_file($fileInfo['tmp_name'], $fullPath)) {
            throw new Exception('Could not move uploaded file to destination path.');
        }

        return new File($fullPath, $firstRowIsHeaders);
    }

    public function getData()
    {
        if (!$this->data) {
            $this->data = $this->loadData();
        }

        return $this->data;
    }

    public function resetData()
    {
        $this->data = [];

        return $this;
    }

    public function getHeaders()
    {
        if (!$this->headers) {
            $this->headers = $this->loadHeaders();
        }

        return $this->headers;
    }

    public function resetHeaders()
    {
        $this->headers = [];

        return $this;
    }

    private function loadData()
    {
        $worksheet = $this->getPhpExcel()->getActiveSheet();

        $data = [];

        foreach ($worksheet->getRowIterator() as $rowNumber => $row) {
            if (1 === $rowNumber && $this->firstRowIsHeaders) {
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];

            /* @var $cell \PHPExcel_Cell */
            foreach ($cellIterator as $cell) {
                $rowData[] = trim($cell->getValue());
            }

            $data[] = $rowData;
        }

        return $data;
    }

    private function loadHeaders()
    {
        $worksheet = $this->getPhpExcel()->getActiveSheet();
        $headers   = [];

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $headers = [];

            /* @var $cell \PHPExcel_Cell */
            foreach ($cellIterator as $cell) {
                if ($this->firstRowIsHeaders) {
                    $headers[] = trim($cell->getValue());
                } else {
                    $headers[] = $cell->getColumn();
                }
            }

            break;
        }

        return $headers;
    }

    private function getPhpExcel()
    {
        if (!$this->phpExcel) {
            $this->phpExcel  = PHPExcel_IOFactory::load($this->fullPath);
        }

        return $this->phpExcel;
    }
}
