<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Helper\SelectSort;

class BootstrapTable extends Table
{
    public function open()
    {
        return '<div class="table-responsive"><table class="bootstrap-table table table-hover">';
    }

    public function close()
    {
        return '</table></div>';
    }

    protected function renderSortLink($content, $fieldId, $direction, SelectSort $sorter = null)
    {
        $request = $this->view->getRequest();
        $caret   = '';

        if (($sorter && $sorter->isSorted() && $sorter->getSortedField()->getQueryStringId() === $fieldId) ||
            $fieldId === urlencode($request->getQuery('sort'))
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
            '<a href="?sort=%s&dir=%s">%s%s</a>',
            $this->view->escapeHtmlAttr($fieldId),
            $this->view->escapeHtmlAttr($direction),
            $this->view->escapeHtml($content),
            $caret
        );
    }
}
