<?php

namespace Dewdrop\ActivityLog;

use DateTimeImmutable;
use Dewdrop\ActivityLog\Entry\Collection;
use Dewdrop\ActivityLog\Exception\EntityNotFound;

class Entry
{
    /**
     * @var Collection
     */
    private $collection = [];

    /**
     * @var array
     */
    private $data;

    public function __construct(Collection $collection, array $data)
    {
        $this->collection = $collection;
        $this->data       = $data;
    }

    public function getId()
    {
        return $this->data['dewdrop_activity_log_id'];
    }

    public function getEntity($handler, $id)
    {
        foreach ($this->getEntities() as $entity) {
            if ($entity['handler'] === $handler && $entity['id'] === (int) $id) {
                return $entity;
            }
        }

        throw new EntityNotFound("Could not find {$handler} entity with ID of {$id}");
    }

    public function getEntities()
    {
        return $this->collection->getEntitiesForEntry($this);
    }

    public function getMessage()
    {
        return $this->data['message'];
    }

    public function getDate()
    {
        return new DateTimeImmutable($this->data['date_created']);
    }
}
