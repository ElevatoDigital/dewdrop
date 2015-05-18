<?php

namespace Admin\{{namespace}};

use Dewdrop\Admin\Page\EditAbstract;
use Dewdrop\Fields\SortedSet;

class Edit extends EditAbstract
{
    private $row;

    public function init()
    {
        $this->row = $this->findRowById('{{model}}');

        $table = $this->row->getTable();

        foreach ($table->getRowColumns() as $column) {
            $this->fields->add($this->row->field($column));
        }
    }

    public function process($response)
    {
        if ($this->inputFilter->isValid()) {
            $response
                ->run('save', array($this->row, 'save'))
                ->setSuccessMessage('Changes saved.')
                ->redirectToAdminPage('Index');
        }
    }

    public function render()
    {
        $this->view->fields = $this->fields;
        $this->view->errors = $this->getErrorsFromInputFilter();
    }
}
