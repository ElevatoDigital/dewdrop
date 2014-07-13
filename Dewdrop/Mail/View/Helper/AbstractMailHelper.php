<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Mail\View\Helper;

use Dewdrop\View\Helper\AbstractHelper;

/**
 * A class all mail view helpers can extend from to ensure they're using
 * the correct partials folder, etc.
 */
abstract class AbstractMailHelper extends AbstractHelper
{
    /**
     * This ensures that helpers on mail views use the correct partials folder.
     *
     * @param string $name
     * @param array $data
     * @return string The rendered output
     */
    public function partial($name, array $data)
    {
        return $this->view->partial($name, $data, __DIR__ . '/partials');
    }
}
