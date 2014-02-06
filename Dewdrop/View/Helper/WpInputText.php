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
 * Render a text input node.  This helper can optionally leverage a
 * \Dewdrop\Db\Field object to set its options.
 *
 * Example usage:
 *
 * <code>
 * echo $this->wpInputText($this->fields->get('animals:latin_name'));
 * </code>
 *
 * @deprecated See \Dewdrop\View\Helper\InputText
 */
class WpInputText extends InputText
{
}
