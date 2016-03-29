<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Db\ManyToMany\Field as ManyToManyField;

class RenderEditControl extends StockPageAbstract
{
    /**
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('create');

        $renderer  = $this->view->editControlRenderer();
        $rowEditor = $this->component->getRowEditor();
        $field     = $this->component->getFields()->get($this->request->getQuery('field'));
        $value     = $this->request->getQuery('value');

        $rowEditor->link();

        if ($field instanceof ManyToManyField) {
            $field->setValue([$value]);
        } else {
            $field->setValue($value);
        }

        $this->component->setShouldRenderLayout(false);

        return $renderer->getControlRenderer()->render($field, 0);
    }
}
