<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper\CellRenderer;

use Dewdrop\Fields\FieldInterface;

/**
 * An interface shared by both CsvCell and TableCell header helpers.
 */
interface HeaderHelperInterface
{
    /**
     * Render the header for the supplied field.
     *
     * @param FieldInterface $field
     * @return string
     */
    public function render(FieldInterface $field);
}
