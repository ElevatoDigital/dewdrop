<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Admin\Component\ComponentInterface;
use Dewdrop\Admin\Component\CrudInterface;

/**
 * This helper recieves a file size in bytes and returns a human readable value.
 */
class FileSize extends AbstractHelper
{
    /**
     * @var array
     */
    protected $units = ['B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB'];

    /**
     * Format the filesize
     *
     * @param int $size The file's size in bytes.
     * @param integer $precision The optional number of decimal digits to round to
     * @return string
     */
    public function direct($size, $precision = 2): string
    {
        $unit = reset($this->units);
        while($size > 1024) {
            $size /= 1024;
            $unit = next($this->units);
        }
        return sprintf('%s %s', round($size, $precision), $unit);
    }
}
