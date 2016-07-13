<?php

namespace Dewdrop\ActivityLog\Handler\Event;

use Dewdrop\ActivityLog\Handler\Event\ManyToManyMessage\Changes;
use Dewdrop\Db\Row;
use Dewdrop\Db\Table;
use Dewdrop\Db\ManyToMany\Field as ManyToManyField;
use Dewdrop\Pimple;

class ManyToManyMessage
{
    /**
     * @var ManyToManyField
     */
    private $field;

    /**
     * @var array
     */
    private $originalValue;

    /**
     * @var Changes
     */
    private $changes;

    /**
     * @var Row
     */
    private $user;

    /**
     * @var Row
     */
    private $subject;

    /**
     * @var string
     */
    private $valueShortcodeName;

    /**
     * @var string
     */
    private $addVerb = 'added';

    /**
     * @var string
     */
    private $removeVerb = 'removed';

    /**
     * @var bool
     */
    private $includeOxfordComma = true;

    public function __construct(Row $subject, Table $valueTable, ManyToManyField $field, array $originalValue = null)
    {
        $this->subject            = $subject;
        $this->field              = $field;
        $this->originalValue      = $originalValue;
        $this->valueShortcodeName = $valueTable->getActivityLogHandler()->getName();
        $this->changes            = new Changes($originalValue, $this->field->getValue());
    }

    public function setUser(Row $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        if (null === $this->user && Pimple::hasResource('user')) {
            $this->user = Pimple::getResource('user');
        }

        return $this->user;
    }

    public function setAddVerb($addVerb)
    {
        $this->addVerb = $addVerb;

        return $this;
    }

    public function setRemoveVerb($removeVerb)
    {
        $this->removeVerb = $removeVerb;

        return $this;
    }

    public function includeOxfordComma()
    {
        $this->includeOxfordComma = true;

        return $this;
    }

    public function omitOxfordComma()
    {
        $this->includeOxfordComma = false;

        return $this;
    }

    public function assembleMessage()
    {
        if ($this->getUser()) {
            return $this->assembleUserMessage($this->getUser());
        } else {
            return $this->assembleAnonymousMessage();
        }
    }

    public function assembleUserMessage(Row $user)
    {
        $lists = [];

        if ($this->changes->hasAdditions()) {
            $lists[] = $this->addVerb . ' ' . $this->assembleList($this->changes->getAdditions());
        }

        if ($this->changes->hasRemovals()) {
            $lists[] = $this->removeVerb . ' ' . $this->assembleList($this->changes->getRemovals());
        }

        $lists = implode(' and ', $lists);

        return "{$user->shortcode()} {$lists} on {$this->field->getLabel()} for {$this->subject->shortcode()}.";
    }

    public function assembleAnonymousMessage()
    {
        return '';
    }

    public function __toString()
    {
        return $this->assembleMessage();
    }

    private function assembleList(array $ids)
    {
        if (!count($ids)) {
            return null;
        }

        $shortcodes = [];

        foreach ($ids as $id) {
            $shortcodes[] = "[{$this->valueShortcodeName} id={$id}]";
        }

        return $this->joinListItems($shortcodes);
    }

    private function joinListItems(array $shortcodes)
    {
        if (1 === count($shortcodes)) {
            return current($shortcodes);
        } else if (2 === count($shortcodes)) {
            return implode(' and ', $shortcodes);
        } else {
            $lastItem = array_pop($shortcodes);

            return implode(', ', $shortcodes) . ($this->includeOxfordComma ? ',' : '') . " and {$lastItem}";
        }
    }
}
