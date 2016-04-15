<?php

namespace Dewdrop\ActivityLog;

use Dewdrop\ActivityLog\Exception\HandlerNotFound;
use Dewdrop\ActivityLog\Handler\HandlerInterface;
use Dewdrop\Db\Table;
use Dewdrop\Paths;
use Dewdrop\Pimple;

class HandlerResolver
{
    /**
     * @var Paths
     */
    private $paths;

    /**
     * @var array
     */
    private $handlerInstances = [];

    /**
     * @var bool
     */
    private $pathSearchPerformed = false;

    public function __construct(Paths $paths = null)
    {
        $this->paths = ($paths ?: Pimple::getResource('paths'));
    }

    public function registerHandler(HandlerInterface $handler)
    {
        $this->handlerInstances[] = $handler;

        return $this;
    }

    /**
     * @param string $name
     * @return HandlerInterface
     * @throws HandlerNotFound
     */
    public function resolve($name)
    {
        $this->searchSystemPathsForHandlers();

        /* @var $handler HandlerInterface */
        foreach ($this->handlerInstances as $handler) {
            if ($name === $handler->getName()) {
                return $handler;
            }
        }

        /* @var $handler HandlerInterface */
        foreach ($this->handlerInstances as $handler) {
            if (in_array($name, $handler->getAliases())) {
                return $handler;
            }
        }

        throw new HandlerNotFound("No activity log handler matching the name '{$name}' could be found.");
    }

    /**
     * @param string $fullyQualifiedName
     * @return HandlerInterface
     * @throws HandlerNotFound
     */
    public function resolveByFullyQualifiedName($fullyQualifiedName)
    {
        $this->searchSystemPathsForHandlers();

        /* @var $handler HandlerInterface */
        foreach ($this->handlerInstances as $handler) {
            if ($handler->getFullyQualifiedName() === $fullyQualifiedName) {
                return $handler;
            }
        }

        throw new HandlerNotFound(
            "No activity log handler matching the fully qualified name '{$fullyQualifiedName}' could be found."
        );
    }

    private function searchSystemPathsForHandlers()
    {
        if ($this->pathSearchPerformed) {
            return;
        }

        $this->pathSearchPerformed = true;

        $this->searchPathForHandlers($this->paths->getActivityLog(), 'ActivityLog');
        $this->searchPathForHandlers($this->paths->getModels(), 'Model');
    }

    private function searchPathForHandlers($path, $namespacePrefix)
    {
        if (!file_exists($path) || !is_dir($path)) {
            return;
        }

        $files = glob("{$path}/*.php");

        foreach ($files as $file) {
            $baseName  = basename($file, '.php');
            $className = "\\{$namespacePrefix}\\{$baseName}";
            $object    = new $className();

            if ($object instanceof Table) {
                $object = $object->getActivityLogHandler();
            }

            $this->registerHandler($object);
        }
    }
}
