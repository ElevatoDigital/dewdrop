<?php

namespace Dewdrop\Exception\View\Helper;

use Dewdrop\Fields;
use Dewdrop\Fields\Helper\TableCell\Content as TableCell;
use Dewdrop\View\Helper\AbstractHelper;

class Trace extends AbstractHelper
{
    public function direct(array $trace)
    {
        return $this->view->partial(
            'trace.phtml',
            ['trace' => $trace, 'fields' => $this->buildFields()],
            __DIR__ . '/partials'
        );
    }

    private function buildFields()
    {
        $fields = new Fields();

        $fields
            ->add('file')
                ->setLabel('File')
                ->setVisible(true)
                ->assignHelperCallback(
                    'TableCell.Content',
                    function (TableCell $helper, array $rowData) {
                        if (!isset($rowData['file'])) {
                            return null;
                        }

                        return $helper->getView()->escapeHtml($rowData['file']);
                    }
                )
            ->add('line')
                ->setLabel('Line')
                ->setVisible(true)
                ->assignHelperCallback(
                    'TableCell.Content',
                    function (TableCell $helper, array $rowData) {
                        if (!isset($rowData['line'])) {
                            return null;
                        }

                        return $helper->getView()->escapeHtml($rowData['line']);
                    }
                )
            ->add('function')
                ->setLabel('Function')
                ->setVisible(true)
                ->assignHelperCallback(
                    'TableCell.Content',
                    function (TableCell $helper, array $rowData) {
                        $out = '';

                        if (!isset($rowData['class'])) {
                            $out .= $helper->getView()->escapeHtml($rowData['function']);
                        } else {
                            $out .= $helper->getView()->escapeHtml(
                                "{$rowData['class']}{$rowData['type']}{$rowData['function']}"
                            );
                        }

                        $out .= '(';

                        $argStrings = [];

                        foreach ($rowData['args'] as $arg) {
                            if (is_array($arg)) {
                                $argStrings[] = 'Array';
                            } elseif (is_object($arg)) {
                                $argStrings[] = get_class($arg);
                            } else {
                                $argStrings[] = var_export($arg, true);
                            }
                        }

                        $out .= implode(', ', $argStrings);
                        $out .= ')';

                        return $out;
                    }
                );

        return $fields;
    }
}
