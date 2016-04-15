<?php

namespace Dewdrop\ActivityLog\Entry;

use Countable;
use Dewdrop\ActivityLog\DbGateway;
use Dewdrop\ActivityLog\Entry;
use Iterator;

class Collection implements Iterator, Countable
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var DbGateway
     */
    private $dbGateway;

    /**
     * @var array
     */
    private $entitiesByMessageId = null;

    public function __construct(DbGateway $dbGateway, array $data)
    {
        $this->data      = $data;
        $this->dbGateway = $dbGateway;
    }

    public function getEntitiesForEntry(Entry $entry)
    {
        $entities = $this->fetchEntities();

        if (!array_key_exists($entry->getId(), $entities)) {
            return [];
        } else {
            return $entities[$entry->getId()];
        }
    }

    private function fetchEntities()
    {
        if (!is_array($this->entitiesByMessageId)) {
            $this->entitiesByMessageId = $this->dbGateway->fetchEntitiesForEntries($this->data);
        }

        return $this->entitiesByMessageId;
    }

    public function current()
    {
        return new Entry($this, current($this->data));
    }

    public function next()
    {
        $next = next($this->data);

        if (false === $next) {
            return false;
        } else {
            return new Entry($this, $next);
        }
    }

    public function key()
    {
        return key($this->data);
    }

    public function valid()
    {
        $key = key($this->data);
        return (null !== $key && false !== $key);
    }

    public function rewind()
    {
        reset($this->data);
    }

    public function count()
    {
        return count($this->data);
    }
}
