<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

/**
 * Render an edit form, with its associated chrome, for the WP admin area.
 *
 * Example usage:
 *
 * <pre>
 * echo $this->editForm()->open('Add New Animal');
 *
 * echo $this->wpEditRow()->open($this->fields->get('animal:latin_name'));
 * echo $this->inputText($this->fields->get('animal:latin_name'));
 * echo $this->wpEditRow()->close();
 *
 * echo $this->editForm()->close();
 * </pre>
 */
class EditForm extends AbstractHelper
{
    /**
     * Open the form.
     *
     * If no action is provided, the form will submit to the current page.
     *
     * @param string $title
     * @param array $errors
     * @param string $method
     * @param string $action
     * @return string
     */
    public function open($title, $errors = array(), $method = 'POST', $action = null)
    {
        return $this->partial(
            'edit-form-open.phtml',
            array(
                'wrap'   => $this->view->wrap()->open(),
                'title'  => $title,
                'method' => $method,
                'errors' => $errors,
                'action' => ($action ?: $_SERVER['REQUEST_URI'])
            )
        );
    }

    /**
     * Close the form.
     *
     * You can optionally provide a different title for the submit button.
     *
     * @param string $buttonTitle
     * @return string
     */
    public function close($buttonTitle = 'Save Changes')
    {
        return $this->partial(
            'edit-form-close.phtml',
            array(
                'buttonTitle' => $buttonTitle,
                'wrap'        => $this->view->wrap()->close()
            )
        );
    }
}
