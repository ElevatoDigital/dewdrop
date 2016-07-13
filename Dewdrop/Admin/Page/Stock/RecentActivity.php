<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\ActivityLog;
use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Pimple;

class RecentActivity extends PageAbstract
{
    /**
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * @var ActivityLog
     */
    private $activityLog;

    /**
     * @var int
     */
    private $pageSize = 50;

    public function setActivityLog(ActivityLog $activityLog)
    {
        $this->activityLog = $activityLog;

        return $this;
    }

    public function getActivityLog()
    {
        if (!$this->activityLog) {
            $this->activityLog = Pimple::getResource('activity-log');
        }

        return $this->activityLog;
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function render()
    {
        $handler     = $this->component->getActivityLogHandler();
        $currentPage = $this->request->getQuery('listing-page', 1);

        $criteria = [
            'handlers' => $handler,
            'limit'    => $this->pageSize,
            'offset'   => ($currentPage - 1) * $this->pageSize
        ];

        if ($this->request->getQuery('id')) {
            $entity = $handler->createEntity($this->request->getQuery('id'));
            $criteria['entities'] = $entity;
            $this->view->assign('entity', $entity);
        }

        $entries = $this->getActivityLog()->getEntries($criteria);

        $this->view
            ->assign('entries', $entries)
            ->assign('title', $this->component->getTitle())
            ->assign('entryCount', $entries->getTotalCount())
            ->assign('pageSize', $this->pageSize)
            ->assign('currentPage', $currentPage);
    }
}
