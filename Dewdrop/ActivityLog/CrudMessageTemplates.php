<?php

namespace Dewdrop\ActivityLog;

use Dewdrop\ActivityLog\Exception\HandlerNotFound;
use Dewdrop\ActivityLog\Exception\TemplateNotFound;
use Dewdrop\ActivityLog\Exception\TemplateValuesIncomplete;
use Dewdrop\Auth\UserInterface as AuthUserInterface;
use Dewdrop\Pimple;

class CrudMessageTemplates
{
    private $summaries = [
        'create'           => 'Create %singularTitle%',
        'edit'             => 'Edit %singularTitle%',
        'delete'           => 'Delete %singularTitle%',
        'restore'          => 'Restore %singularTitle%',
        'import'           => 'Import %pluralTitle%',
        'export'           => 'Export %pluralTitle%',
        'reorder-items'    => 'Reorder %pluralTitle%',
        'rearrange-fields' => 'Rearrange %singularTitle% Fields'
    ];

    private $anonymousMessages = [
        'create'           => 'Created %entity%.',
        'edit'             => 'Edited %entity%.',
        'delete'           => 'Deleted %entity%.',
        'restore'          => 'Restored %entity%.',
        'import'           => 'Imported %count% %pluralTitle% from %filename%.',
        'export'           => 'Exported %pluralTitle%.',
        'reorder-items'    => 'Reordered %pluralTitle%.',
        'rearrange-fields' => 'Rearranged fields for %pluralTitle%.'
    ];

    private $userMessages = [
        'create'           => '%user% created %entity%.',
        'edit'             => '%user% updated %entity%.',
        'delete'           => '%user% deleted %entity%.',
        'restore'          => '%user% restored %entity%.',
        'import'           => '%user% imported %count% %pluralTitle% from %filename%.',
        'export'           => '%user% exported %pluralTitle%.',
        'reorder-items'    => '%user% reordered %pluralTitle%.',
        'rearrange-fields' => '%user% rearranged fields for %pluralTitle%.'
    ];

    /**
     * @var AuthUserInterface
     */
    private $user;

    /**
     * @var HandlerResolver
     */
    private $handlerResolver;

    public function __construct(HandlerResolver $handlerResolver, AuthUserInterface $user = null)
    {
        $this->user            = $user;
        $this->handlerResolver = $handlerResolver;
    }

    public function setTemplate($name, $summary, $anonymousMessage, $userMessage)
    {
        if (!array_key_exists($name, $this->summaries)) {
            throw new TemplateNotFound("Message template not found with name '{$name}'.");
        }

        $this->summaries[$name]         = $summary;
        $this->anonymousMessages[$name] = $anonymousMessage;
        $this->userMessages[$name]      = $userMessage;
    }

    public function getSummary($name, array $templateValues)
    {
        if (!array_key_exists($name, $this->summaries)) {
            throw new TemplateNotFound("Message summary template not found with name '{$name}'.");
        }

        return $this->renderTemplateTags($this->summaries[$name], $templateValues);
    }

    public function getMessage($name, array $templateValues)
    {
        if (!array_key_exists($name, $this->anonymousMessages)) {
            throw new TemplateNotFound("Message template not found with name '{$name}'.");
        }

        $user = $this->user;

        if (!$user && Pimple::hasResource('user')) {
            $user = Pimple::getResource('user');
        }

        $userHandler = null;

        try {
            $userHandler = $this->handlerResolver->resolve('user');
        } catch (HandlerNotFound $e) {
            // OK to proceed without the user handler.  Will just treat as anonymous.
        }

        if (!$user || !$userHandler) {
            $template = $this->anonymousMessages[$name];
        } else {
            $template = $this->userMessages[$name];

            // Automatically include %user% value when available.
            $templateValues['%user%'] = $userHandler->createEntity($user->getId());
        }

        return $this->renderTemplateTags($template, $templateValues);
    }

    private function renderTemplateTags($template, array $templateValues)
    {
        $out = str_replace(array_keys($templateValues), $templateValues, $template);

        if (preg_match('/%[a-zA-Z\-0-9]+%/', $out)) {
            throw new TemplateValuesIncomplete(
                "You must supply values for all the placeholders in the template string: {$template}."
            );
        }

        return $out;
    }
}
