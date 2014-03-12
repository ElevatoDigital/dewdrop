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
 * <code>
 * echo $this->editForm()->open('Add New Animal');
 *
 * echo $this->wpEditRow()->open($this->fields->get('animal:latin_name'));
 * echo $this->inputText($this->fields->get('animal:latin_name'));
 * echo $this->wpEditRow()->close();
 *
 * echo $this->editForm()->close();
 * </code>
 *
 * @deprecated See \Dewdrop\View\Helper\EditForm
 */
class WpEditForm extends EditForm
{
}
