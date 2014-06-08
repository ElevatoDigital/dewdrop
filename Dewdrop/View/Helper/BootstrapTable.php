<?php

namespace Dewdrop\View\Helper;

class BootstrapTable extends Table
{
    public function open()
    {
        return '<div class="table-responsive"><table class="table">';
    }

    public function close()
    {
        return '</table></div>';
    }

    protected function renderSortLink($content, $fieldId, $direction)
    {
        $request = $this->view->getRequest();
        $caret   = '';

        if ($fieldId === urlencode($request->getQuery('sort'))) {
            $activeDir = ('asc' === $request->getQuery('dir') ? 'asc' : 'desc');

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
