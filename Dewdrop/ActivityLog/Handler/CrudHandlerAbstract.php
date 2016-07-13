<?php

namespace Dewdrop\ActivityLog\Handler;

use Dewdrop\ActivityLog;
use Dewdrop\ActivityLog\CrudMessageTemplates;
use Dewdrop\Pimple;

abstract class CrudHandlerAbstract extends HandlerAbstract
{
    /**
     * @var CrudMessageTemplates
     */
    private $messageTemplates;

    public function __construct(CrudMessageTemplates $messageTemplates = null)
    {
        parent::__construct();

        $this->messageTemplates = ($messageTemplates ?: Pimple::getResource('activity-log.crud-message-templates'));
    }

    public function create($id)
    {
        $this->logUsingMessageTemplate(
            'create',
            ['%entity%' => $this->createEntity($id)]
        );
    }

    public function edit($id)
    {
        $this->logUsingMessageTemplate(
            'edit',
            ['%entity%' => $this->createEntity($id)]
        );
    }

    public function delete($id)
    {
        $this->logUsingMessageTemplate(
            'delete',
            ['%entity%' => $this->createEntity($id)]
        );
    }

    public function restore($id)
    {
        $this->logUsingMessageTemplate(
            'restore',
            ['%entity%' => $this->createEntity($id)]
        );
    }

    public function import($count, $filename)
    {
        $this->logUsingMessageTemplate(
            'import',
            ['%count%' => $count, '%filename%' => $filename]
        );
    }

    public function export()
    {
        $this->logUsingMessageTemplate('export');
    }

    public function reorderItems()
    {
        $this->logUsingMessageTemplate('reorder-items');
    }

    public function rearrangeFields()
    {
        $this->logUsingMessageTemplate('rearrange-fields');
    }

    private function logUsingMessageTemplate($name, array $templateValues = [])
    {
        if (!array_key_exists('%pluralTitle%', $templateValues)) {
            $templateValues['%pluralTitle%'] = $this->getModel()->getPluralTitle();
        }

        if (!array_key_exists('%singularTitle%', $templateValues)) {
            $templateValues['%singularTitle%'] = $this->getModel()->getSingularTitle();
        }

        $this->log(
            $this->messageTemplates->getSummary($name, $templateValues),
            $this->messageTemplates->getMessage($name, $templateValues)
        );
    }
}
