<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use HtmlNode\Node;

/**
 * Render pagination controls using Bootstrappy markup.
 */
class Pagination extends AbstractHelper
{
    /**
     * Render pagination.
     *
     * @param integer $rowCount The total number of records available.
     * @param integer $pageSize The number of records to show on each page.
     * @param integer $page The currently selected page.
     * @param string $title The title to use in the record count.
     * @return string
     */
    public function direct($rowCount, $pageSize, $page, $title = 'Records')
    {
        $pageCount = ceil($rowCount / $pageSize);

        $node = Node::create('div')->addClass('dewdrop-pagination')->addClass('text-center');
        $out  = '';

        if ($rowCount > $pageSize) {

            $out .= "<div class=\"page-current-of-all\">Page {$page} of {$pageCount}</div>" .
                '<ul class="pagination">';

            $out .= '<li' . ($page > 1 ? '' : ' class="disabled"') . '><a href="' . $this->url($page - 1) .
                '">&laquo; Previous</a></li>';

            $j = 0;
            for ($i = 1; $i <= $pageCount; $i++) {

                $display = false;

                if ($page < 7 && $i <= 10) {
                    // Current page is in the first 6, show the first 10 pages
                    $display = true;

                } elseif ($page > $pageCount - 6 && $i >= $pageCount - 10) {

                    // Current page is in the last 6, show the last 10 pages
                    $display = true;

                } elseif ($i < 3 || $i > $pageCount - 2 || abs($page - $i) <= 3) {

                    // Always show the first 2, last 2, and middle 6 pages
                    $display = true;
                }

                if ($display) {

                    if ($j + 1 !== $i) {
                        // ellipses
                        $out .= '<li class="disabled"><a href="#">...</a></li>';
                    }

                    $out .= '<li' . ($i === $page ? ' class="disabled"' : '' ) . '><a href="' . $this->url($i) .
                        "\">{$i}</a></li>";

                    $j = $i;
                }
            }

            $out .= '<li' . ($page < $pageCount ? '' : ' class="disabled"') . '><a href="' . $this->url($page + 1) .
                '">Next &raquo;</a></li>';

            $out .= '</ul>';
        }

        $out .= sprintf(
            '<div class="row-count">%d %s</div>',
            (int) $rowCount,
            $this->view->escapeHtml($title)
        );

        $node->setHtml($out);

        return $node;
    }

    /**
     * Get a URL for the pagination links.
     *
     * @param int $page
     * @return string
     */
    protected function url($page)
    {
        $request = clone $this->view->getRequest();

        return $request
            ->setQuery('listing-page', $page)
            ->getUrl();
    }
}
