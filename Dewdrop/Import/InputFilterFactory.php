<?php

namespace Dewdrop\Import;

use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\File\MimeType;
use Zend\Validator\File\UploadFile;

class InputFilterFactory
{
    public static function createInstance()
    {
        $inputFilter = new InputFilter();

        $input = new Input('file');
        $input
            ->setRequired(true)
            ->setAllowEmpty(false);
        $inputFilter->add($input);

        $mimeType = new MimeType();

        $mimeType->setMimeType(
            [
                'text/csv',
                'application/vnd.ms-excel',
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
