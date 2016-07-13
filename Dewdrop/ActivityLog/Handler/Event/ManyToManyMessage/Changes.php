<?php

namespace Dewdrop\ActivityLog\Handler\Event\ManyToManyMessage;

class Changes
{
    /**
     * @var array
     */
    private $originalValue;

    /**
     * @var array
     */
    private $currentValue;

    /**
     * @var array
     */
    private $removals;

    /**
     * @var array
     */
    private $additions;

    public function __construct(array $originalValue = null, array $currentValue = null)
    {
        $this->originalValue = (is_array($originalValue) ? $originalValue : []);
        $this->currentValue  = (is_array($currentValue) ? $currentValue : []);

        $this->removals  = array_diff($this->originalValue, $this->currentValue);
        $this->additions = array_diff($this->currentValue, $this->originalValue);
    }

    public function hasAdditions()
    {
        return 0 < count($this->additions);
    }

    public function hasRemovals()
    {
        return 0 < count($this->removals);
    }

    public function getAdditions()
    {
        return $this->additions;
    }

    public function getRemovals()
    {
        return $this->removals;
    }
}
