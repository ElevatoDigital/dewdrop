<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Helper\SelectSort;

/**
 * Render a table using classes and markup consistent with Boostrap's
 * documentation.  Note that this helper will wrap your table in a
 * .table-responsive div so that it is still reasonably usable on a
 * small screen.
 */
class BootstrapTable extends Table
{
    /**
     * Table wrapper's id HTML attribute.
     *
     * @var string
     */
    protected $tableWrapperId;

    /**
     * Open the table for rendering.  Notice the .table-responsive wrapper.
     *
     * @return string
     */
    public function open()
    {
        $tableWrapperIdAttr = '';

        if (null !== $this->tableWrapperId) {
            $tableWrapperIdAttr = ' id="' . $this->view->escapeHtmlAttr($this->tableWrapperId) . '"';
        }

        return <<<HTML
            <div class="table-responsive"{$tableWrapperIdAttr}><table class="bootstrap-table table table-hover">
HTML;
    }

    /**
     * Close the table itself and its .table-responsive wrapper.
     *
     * @return string
     */
    public function close()
    {
        return '</table></div>';
    }

    /**
     * Return the table wrapper's id HTML attribute.
     *
     * @return string|null
     */
    public function getTableWrapperId()
    {
        return $this->tableWrapperId;
    }

    /**
     * Set the table wrapper's id HTML attribute
     * @param string $tableWrapperId
     * @return BootstrapTable
     */
    public function setTableWrapperId($tableWrapperId)
    {
        $this->tableWrapperId = (string) $tableWrapperId;

        return $this;
    }

    /**
     * Render a sorting link for a particular column.  If the collumn is
     * currently selected, which we can detect using a SelectSort helper, we use the
     * carets provided in Bootstrap to indicate in which direction it is sorted.
     *
     * @param string $content
     * @param string $fieldId
     * @param string $direction
     * @param SelectSort $sorter
     * @return string
     */
    protected function renderSortLink($content, $fieldId, $direction, SelectSort $sorter = null)
    {
        $request = $this->view->getRequest();
        $caret   = '';

        if (($sorter && $sorter->isSorted() && $sorter->getSortedField()->getQueryStringId() === $fieldId) ||
            $fieldId === $request->getQuery('sort')
        ) {
            if ($sorter) {
                $activeDir = strtolower($sorter->getSortedDirection());
            } else {
                $activeDir = ('asc' === $request->getQuery('dir') ? 'asc' : 'desc');
            }

            if ('asc' === $activeDir) {
                $caret = ' <span class="caret caret-up"></span>';
            } else {
                $caret = '<span class="caret"></span>';
            }
        }

        return sprintf(
            '<a href="%s">%s%s</a>',
            $this->view->escapeHtmlAttr($this->url($fieldId, $direction)),
            $this->view->escapeHtml($content),
            $caret
        );
    }

    /**
     * Generate a URL for sorting.
     *
     * @param string $id
     * @param string $direction
     * @return string
     */
    protected function url($id, $direction)
    {
        $request = clone $this->view->getRequest();

        return $request
            ->setQuery('sort', $id)
            ->setQuery('dir', $direction)
            ->getUrl();
    }
}
