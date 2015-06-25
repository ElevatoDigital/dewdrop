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
 * Render a checkbox node.  This helper can optionally leverage a
 * \Dewdrop\Db\Field object to set its options.
 *
 * Example usage:
 *
 * <pre>
 * echo $this->wpInputCheckbox($this->fields->get('animals:is_mammals'));
 * </pre>
 *
 * @deprecated See \Dewdrop\View\Helper\InputCheckbox
 */
class WpInputCheckbox extends InputCheckbox
{
}
