<?php

namespace Dewdrop\ActivityLog;

use Dewdrop\ActivityLog\Exception\InvalidEntityPrimaryKey;
use Dewdrop\ActivityLog\Handler\HandlerInterface;
use Thunder\Shortcode\Parser\RegularParser as ShortcodeParser;

class Entity
{
    /**
     * @var ShortcodeParser
     */
    private static $shortcodeParser;

    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @var int
     */
    private $primaryKeyValue;

    /**
     * @var string
     */
    private $title;

    public function __construct(HandlerInterface $handler, $primaryKeyValue)
    {
        if (!$primaryKeyValue) {
            throw new InvalidEntityPrimaryKey('Must provide a non-zero integer primary key value for an entity.');
        }

        $this->handler         = $handler;
        $this->primaryKeyValue = $primaryKeyValue;
    }

    public static function fromShortcode($shortcode, HandlerResolver $handlerResolver)
    {
        if (!self::$shortcodeParser) {
            self::$shortcodeParser = new ShortcodeParser();
        }

        $shortcodes = self::$shortcodeParser->parse($shortcode);

        /* @var $shortcode \Thunder\Shortcode\Shortcode\AbstractShortcode */
        $shortcode  = current($shortcodes);

        return new Entity(
            $handlerResolver->resolve($shortcode->getName()),
            $shortcode->getParameter('id')
        );
    }

    public function getLink()
    {
        return $this->handler->renderLinkUrl($this->primaryKeyValue);
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        if (!$this->title) {
            $this->title = $this->handler->renderTitleText($this->primaryKeyValue);
        }

        return $this->title;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getFullyQualifiedName()
    {
        return $this->handler->getFullyQualifiedName();
    }

    public function getPrimaryKeyValue()
    {
        return $this->primaryKeyValue;
    }

    public function assembleShortCode()
    {
        return sprintf('[%s id=%d]', $this->handler->getName(), $this->primaryKeyValue);
    }

    public function __toString()
    {
        return $this->assembleShortCode();
    }
}
