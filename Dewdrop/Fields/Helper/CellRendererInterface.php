<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

/**
 * This is an interface shared by the CsvCell and TableCell classes.  It allows
 * other classes to interact with with CsvCell or TableCell objects interchangeably.
 */
interface CellRendererInterface
{
    /**
     * Get the helper responsible for rendering the content in body cells.
     *
     * @return \Dewdrop\Fields\Helper\CellRenderer\ContentHelperInterface
     */
    public function getContentRenderer();

    /**
     * Get the helper responsible for rendering the content in header cells.
     *
     * @return \Dewdrop\Fields\Helper\CellRenderer\HeaderHelperInterface
     */
    public function getHeaderRenderer();
}
