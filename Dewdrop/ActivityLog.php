<?php

namespace Dewdrop;

use Dewdrop\ActivityLog\DbGateway;
use Dewdrop\ActivityLog\Entry\EntryReader;
use Dewdrop\ActivityLog\Exception\InvalidShortcode as InvalidShortcodeException;
use Dewdrop\ActivityLog\Handler\HandlerInterface;
use Dewdrop\ActivityLog\HandlerResolver;
use Thunder\Shortcode\Parser\RegularParser as ShortcodeParser;

class ActivityLog
{
    /**
     * @var DbGateway
     */
    private $dbGateway;

    /**
     * @var HandlerResolver
     */
    private $handlerResolver;

    /**
     * @var ShortcodeParser
     */
    private $shortcodeParser;

    /**
     * ActivityLog constructor.
     * @param DbGateway $dbGateway
     */
    public function __construct(
        DbGateway $dbGateway,
        HandlerResolver $handlerResolver,
        ShortcodeParser $shortcodeParser = null
    ) {
        $this->dbGateway       = $dbGateway;
        $this->handlerResolver = $handlerResolver;
        $this->shortcodeParser = ($shortcodeParser ?: new ShortcodeParser());
    }

    /**
     * @param HandlerInterface $handler
     * @return $this
     */
    public function registerHandler(HandlerInterface $handler)
    {
        $this->handlerResolver->registerHandler($handler);
        return $this;
    }

    /**
     * @param string $name
     * @return HandlerInterface
     * @throws ActivityLog\Exception\HandlerNotFound
     */
    public function handler($name)
    {
        $handler = $this->handlerResolver->resolve($name);
        $handler->setActivityLog($this);
        return $handler;
    }

    /**
     * @param string $fullyQualifiedName
     * @return HandlerInterface
     * @throws ActivityLog\Exception\HandlerNotFound
     */
    public function handlerByFullyQualifiedName($fullyQualifiedName)
    {
        $handler = $this->handlerResolver->resolveByFullyQualifiedName($fullyQualifiedName);
        $handler->setActivityLog($this);
        return $handler;
    }

    public function write($summary, $message)
    {
        return $this->log($summary, $message);
    }

    public function log($summary, $message)
    {
        $shortcodes = $this->shortcodeParser->parse($message);
        $entities   = [];

        foreach ($shortcodes as $shortcode) {
            if (!$shortcode->hasParameter('id') || !$shortcode->getParameter('id')) {
                throw new InvalidShortcodeException(
                    "Shortcode does not contain required 'id' parameter: {$shortcode->getText()}."
                );
            }

            $name      = $shortcode->getName();
            $handler   = $this->handler($name);
            $id        = $shortcode->getParameter('id');
            $titleText = $handler->renderTitleText($id);

            $entities[] = [
                'handler'           => $handler->getFullyQualifiedName(),
                'title_text'        => $titleText,
                'primary_key_value' => $id
            ];
        }

        $this->dbGateway->insertEntry($summary, $message, $entities);

        return $this;
    }

    public function getEntries(array $options = [])
    {
        $reader = new EntryReader($this->dbGateway, $this->handlerResolver);
        $reader->setOptions($options);
        return $reader;
    }
}
