<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Fields;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\TableCell;
use Dewdrop\Db\Select;

class Counts extends PageAbstract
{
    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('count-fields');
    }

    public function render()
    {
        $fields      = $this->component->getFields();
        $selected    = null;
        $countFields = null;

        if ($this->request->getQuery('group_field')) {
            $selected    = $fields->getVisibleFields()->getByQueryStringId($this->request->getQuery('group_field'));
            $countFields = $this->buildCountFields($selected);
        }

        $this->view->assign(
            [
                'title'         => $this->component->getTitle(),
                'model'         => $this->component->getPrimaryModel(),
                'fields'        => $this->component->getFields(),
                'listing'       => $this->component->getListing(),
                'selectedField' => ($selected ? $selected->getQueryStringId() : null),
                'countFields'   => $countFields,
                'data'          => ($selected ? $this->fetchData($selected) : []),
            ]
        );
    }

    private function buildCountFields(FieldInterface $selected)
    {
        $fields = new Fields();

        $fields
            ->add('html')
                ->setLabel($selected->getLabel())
                ->setVisible(true)
                ->assignHelperCallback(
                    'TableCell.Content',
                    function (TableCell\Content $helper, array $rowData) {
                        // Not escaping here because we assume it was escaped by the original renderer in fetchData()
                        return $rowData['html'];
                    }
                )
            ->add('count')
                ->setLabel('Count')
                ->setVisible(true)
                ->assignHelperCallback(
                    'TableCell.Content',
                    function (TableCell\Content $helper, array $rowData) {
                        return $helper->getView()->escapeHtml($rowData['count']);
                    }
                );

        return $fields;
    }

    private function fetchData(FieldInterface $selected)
    {
        $listing = $this->component->getListing();

        $listing->getSelectModifierByName('SelectSort')
            ->setDefaultField($selected)
            ->setDefaultDirection('asc');

        $listing->removeSelectModifierByName('SelectPaginate');

        $data     = $listing->fetchData($this->component->getFields());
        $renderer = new TableCell($this->view);
        $counts   = [];
        $out      = [];

        foreach ($data as $row) {
            $tableCellContent = $renderer->getContentRenderer()->render($selected, $row, 0, 0);

            if (!array_key_exists($tableCellContent, $counts)) {
                $counts[$tableCellContent] = 0;
            }

            $counts[$tableCellContent] += 1;
        }

        foreach ($counts as $html => $count) {
            $out[] = ['html' => $html, 'count' => $count];
        }

        return $out;
    }
}
