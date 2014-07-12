<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Exception;
use Dewdrop\Fields\UserInterface;
use Dewdrop\Pimple;

class Permissions
{
    private $component;

    private $registeredPermissions = array();

    private $settings;

    private $lockedSettings;

    public function __construct(ComponentAbstract $component)
    {
        $this->component = $component;

        $this
            ->register('access', 'Allow access to the ' . $this->component->getTitle() . ' component')
            ->set('access', true);

        $this
            ->register('display-menu', 'Show the ' . $this->component->getTitle() . ' component in the menu')
            ->set('display-menu', true);

        if ($this->component instanceof CrudInterface) {
            $this->registerAndSetDefaultsForCrudInterface();
        }
    }

    public function registerAndSetDefaultsForCrudInterface()
    {
        $plural   = strtolower($this->component->getPrimaryModel()->getPluralTitle());
        $singular = strtolower($this->component->getPrimaryModel()->getSingularTitle());

        $crudPermissions = array(
            'adjust-columns' => 'Adjust the columns that are visible on the main listing',
            'create'         => "Create new {$plural}",
            'custom-views'   => 'Create and edit custom views',
            'debug'          => 'Use debugging tools',
            'edit'           => "Edit existing {$plural}",
            'export'         => 'Export data to a file',
            'filter'         => "Filter {$plural}",
            'sort-fields'    => "Sort and group {$singular} fields",
            'notifications'  => "Subscribe to be notified when {$plural} are added or updated",
            'view'           => "See an individual {$singular} in detail",
            'view-listing'   => "See the full {$plural} listing"
        );

        foreach ($crudPermissions as $name => $description) {
            $this
                ->register($name, $description)
                ->set($name, true);
        }

        $this->set('debug', Pimple::getResource('debug'));

        return $this;
    }

    public function register($name, $description)
    {
        $this->registeredPermissions[$name] = $description;

        return $this;
    }

    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    public function can($name, $throwExceptionOnFail = false)
    {
        if (!array_key_exists($name, $this->registeredPermissions)) {
            throw new Exception("Could not find permission with name '{$name}'");
        }

        $can = $this->settings[$name];

        if (is_array($can)) {
            $allowedRoles = $can;

            $user = (Pimple::getResource('user') ? Pimple::getResource('user') : '');
            $can  = false;

            foreach ($allowedRoles as $role) {
                if ($user && in_array($role, $user->getRoles())) {
                    $can = true;
                    break;
                }
            }
        }

        if (!$can && $throwExceptionOnFail) {
            throw new Exception("Permission denied: {$this->component->getFullyQualifiedName()}/{$name}.");
        }

        return $can;
    }

    public function haltIfNotAllowed($name)
    {
        return $this->can($name, true);
    }

    public function setAll($setting)
    {
        foreach ($this->registeredPermissions as $name => $description) {
            $this->set($name, $setting);
        }

        return $this;
    }

    public function set($name, $setting)
    {
        if (!array_key_exists($name, $this->registeredPermissions)) {
            throw new Exception("Could not find permission with name '{$name}'");
        }

        $this->settings[$name] = $setting;

        return $this;
    }

    public function lock($name, $setting)
    {
        if (!array_key_exists($name, $this->registeredPermissions)) {
            throw new Exception("Could not find permission with name '{$name}'");
        }

        $this->lockedSettings[] = $name;
        $this->settings[$name]  = $setting;

        return $this;
    }
}
