<?php

namespace Dewdrop\ActivityLog;

use DateTimeImmutable;
use Dewdrop\ActivityLog\Entry\Collection;
use Dewdrop\ActivityLog\Exception\EntityNotFound;
use Dewdrop\ActivityLog\Handler\HandlerInterface;

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

    public function getUserInformation()
    {
        return $this->collection->getUserInformationById($this->data['dewdrop_activity_log_user_information_id']);
    }

    public function getEntity(HandlerInterface $handler, $id)
    {
        foreach ($this->getEntities() as $entityData) {
            if ($entityData['handler'] === $handler->getFullyQualifiedName() && $entityData['id'] === (int) $id) {
                $entity = new Entity($handler, $entityData['id']);
                $entity->setTitle($entityData['title']);
                return $entity;
            }
        }

        throw new EntityNotFound("Could not find {$handler->getName()} entity with ID of {$id}");
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
