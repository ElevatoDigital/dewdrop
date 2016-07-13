<?php

namespace Dewdrop\View\Helper;

use Dewdrop\ActivityLog;
use Dewdrop\ActivityLog\Entry;
use Dewdrop\Pimple;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class ActivityLogMessage extends AbstractHelper
{
    public function direct(Entry $entry, ActivityLog $activityLog = null)
    {
        /* @var $resolver \Dewdrop\ActivityLog\HandlerResolver */
        $resolver = Pimple::getResource('activity-log.handler-resolver');

        $handlers = new HandlerContainer();
        $handlers->setDefault(
            function (ShortcodeInterface $s) use ($entry, $resolver) {
                try {
                    $handler = $resolver->resolve($s->getName());
                    $entity  = $entry->getEntity($handler, $s->getParameter('id'));
                    return trim($this->view->activityLogEntity($entity));
                } catch (ActivityLog\Exception\HandlerNotFound $e) {
                    return '<strong>Unknown Type</strong>';
                } catch (ActivityLog\Exception\EntityNotFound $e) {
                    return '<strong>Entity Not in DB</strong>';
                }
            }
        );

        $processor = new Processor(new RegularParser(), $handlers);

        return $processor->process($entry->getMessage());
    }
}
