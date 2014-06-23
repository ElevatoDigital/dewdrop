<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Exception;

class Pagination extends AbstractHelper
{
    /**
     * @return string
     * @throws \Dewdrop\Exception
     */
    public function direct($rowCount, $pageSize, $page, $title = 'Records')
    {
        $pageCount = ceil($rowCount / $pageSize);

        $out = '<div class="dewdrop-pagination text-center">';

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
                        $out .= '<li class="disabled"><a href="#">…</a></li>';
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

        $out .= '</div>';

        return $out;
    }

    /**
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
