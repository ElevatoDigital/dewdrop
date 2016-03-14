<?php

namespace Dewdrop\View\Helper;

class InputFile extends AbstractHelper
{
    public function direct()
    {
        return $this->delegateByArgs(func_get_args(), 'direct');
    }

    public function directArray(array $options)
    {
        $this->view->headScript()->appendFile($this->view->bowerUrl('/dewdrop/www/js/input-file/main.js'));
        $this->view->headLink()->appendStylesheet($this->view->bowerUrl('/dewdrop/www/css/input-file.css'));

        $options = $this->prepareOptionsArray($options);

        if (!$options['id']) {
            $options['id'] = $options['name'];
        }

        if (!$options['buttonTitle']) {
            $options['buttonTitle'] = 'Upload File';
        }

        $options['classes'][] = 'input-file';

        return $this->partial(
            'input-file.phtml',
            [
                'id'            => $options['id'],
                'name'          => $options['name'],
                'classes'       => $options['classes'],
                'value'         => $options['value'],
                'buttonTitle'   => $options['buttonTitle'],
                'fileInputName' => $options['fileInputName'],
                'fileThumbnail' => $options['fileThumbnail'],
                'fileUrl'       => $options['fileUrl'],
                'actionUrl'     => $options['actionUrl']
            ]
        );
    }

    /**
     * Prepare the options array for the directArray() method, checking that
     * required options are set, ensuring "classes" is an array and adding
     * "classes" and "id" to the options array, if they are not present
     * already.
     *
     * @param array $options
     * @return array
     */
    private function prepareOptionsArray(array $options)
    {
        $this
            ->checkRequired($options, array('name', 'value', 'fileInputName', 'actionUrl'))
            ->ensurePresent($options, array('classes', 'id', 'buttonTitle', 'fileThumbnail', 'fileUrl'))
            ->ensureArray($options, array('classes'));

        return $options;
    }
}
