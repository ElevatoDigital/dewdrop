<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

/**
 * This helper adds the Summernote (http://hackerwins.github.io/summernote/)
 * rich text area on top of a normal textarea control.
 */
class Summernote extends BootstrapTextarea
{
    /**
     * Render a textarea tag and add the client-side resources needed for
     * Summernote.
     *
     * @param array $options
     * @return string
     */
    public function directArray(array $options)
    {
        $this->view->headLink()
            ->appendStylesheet($this->view->bowerUrl('/font-awesome/css/font-awesome.min.css'))
            ->appendStylesheet($this->view->bowerUrl('/summernote/dist/summernote.css'));

        $this->view->headScript()->appendFile($this->view->bowerUrl('/dewdrop/www/js/summernote.js'));

        if (!isset($options['classes']) || !is_array($options['classes'])) {
            $options['classes'] = [];
        }

        $options['classes'][] = 'summernote';

        return parent::directArray($options);
    }
}
