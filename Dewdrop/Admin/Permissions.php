<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin;

use Dewdrop\Admin\Component\ComponentInterface;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Exception;
use Dewdrop\Pimple;
use Symfony\Component\Security\Core\Role\Role as SfRole;
use Symfony\Component\Security\Core\User\UserInterface as SfSecurityUserInterface;

/**
 * This class allows you to adjust the permissions for an admin component.  It
 * allows you to disable or enable a permission globally, by role or by callback,
 * much like the permissions-related methods on \Dewdrop\Fields\FieldInterface.
 * By default, there are only two very simple permissions available:
 *
 * 1) access: Can the user access this component at all?
 *
 * 2) display-menu: Should the component be shown in the main admin nav menu?
 *
 * For component's implementing the CrudInterface, they get a number of additional
 * permissions automatically.
 *
 * For permissions specific to your component, call the register() method to add
 * them.
 */
class Permissions
{
    /**
     * The component these permissions apply to.
     *
     * @var Component\ComponentAbstract
     */
    private $component;

    /**
     * What permissions are available to set on this component.
     *
     * @var array
     */
    private $registeredPermissions = array();

    /**
     * The settings applied for the permissions so far.
     *
     * @var array
     */
    private $settings = array();

    /**
     * Any settings that should not be alterable (or even shown) in the UI.
     *
     * @var array
     */
    private $lockedSettings = array();

    /**
     * Provide the component that these permissions should be applied to.
     *
     * @param mixed $component
     * @param null|bool $debug
     * @throws Exception
     */
    public function __construct($component, $debug = null)
    {
        if (!$component instanceof ComponentInterface && !$component instanceof CrudInterface) {
            throw new Exception('Component must be CopmonentInterface or implement CrudInterface');
        }

        $this->component = $component;
        $this->debug     = (null === $debug ? Pimple::getResource('debug') : $debug);

        $this
            ->register('access', 'Allow access to the ' . $this->component->getTitle() . ' component')
            ->set('access', true);

        $this
            ->register('display-menu', 'Show the ' . $this->component->getTitle() . ' component in the menu')
            ->set('display-menu', true);

        if ($this->component instanceof CrudInterface) {
            $this->registerAndSetDefaultsForCrudInterface($this->component);
        }
    }

    /**
     * Register a number of permissions that we make available on CrudInterface
     * components.
     *
     * @param CrudInterface $component
     * @return $this
     */
    public function registerAndSetDefaultsForCrudInterface(CrudInterface $component)
    {
        $plural   = strtolower($component->getPrimaryModel()->getPluralTitle());
        $singular = strtolower($component->getPrimaryModel()->getSingularTitle());

        $crudPermissions = array(
            'adjust-columns' => 'Adjust the columns that are visible on the main listing',
            'create'         => "Create new {$plural}",
            'delete'         => "Delete {$plural}",
            'custom-views'   => 'Create and edit custom views',
            'debug'          => 'Use debugging tools',
            'edit'           => "Edit existing {$plural}",
            'export'         => 'Export data to a file',
            'filter'         => "Filter {$plural}",
            'import'         => 'Import data from a file',
            'count-fields'   => "Count {$plural} while grouping by fields",
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

        // These features are disabled by default.  Can be turned on whenever they're wanted.
        $this->set('count-fields', false);
        $this->set('import', false);

        // @ todo Re-enable these pages once they're complete
        $this->set('custom-views', false);
        $this->set('notifications', false);

        $this->set('debug', $this->debug);

        return $this;
    }

    /**
     * Register a new permission that can be set for the component.  The name
     * will be referenced in code when setting the permission.  The description
     * will be displayed to users in a UI.
     *
     * @param string $name
     * @param string $description
     * @return $this
     */
    public function register($name, $description)
    {
        $this->registeredPermissions[$name] = $description;

        return $this;
    }

    /**
     * Check to see if the given permission is granted to the current user (or
     * anonymous users, if no user resource is available in Pimple.  You can optionally
     * choose to just throw an exception to halt execution when the user doesn't
     * have the requested permission.  This can be convenient when the user
     * can only reach the point where this permission is checked by circumventing
     * the normal navigation provided in the UI (e.g. by manipulating the URL).
     *
     * @throws Exception
     * @param string $name
     * @param boolean $throwExceptionOnFail
     * @return boolean
     */
    public function can($name, $throwExceptionOnFail = false)
    {
        if (!array_key_exists($name, $this->registeredPermissions)) {
            throw new Exception("Could not find permission with name '{$name}'");
        }

        $can = $this->settings[$name];

        if (is_array($can)) {
            $allowedRoles = $can;

            $can  = false;
            $user = null;

            if ($this->component->hasPimpleResource('user')) {
                $user = $this->component->getPimpleResource('user');
            }

            foreach ($allowedRoles as $role) {
                if ($user && in_array($role, $this->getUserRoleValues($user))) {
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

    /**
     * Check the supplied permission and halt execution if it's not allowed.
     *
     * @param string $name
     * @return bool
     */
    public function haltIfNotAllowed($name)
    {
        return $this->can($name, true);
    }

    /**
     * Set all registered permissions to the supplied value.  Useful if you want
     * different defaults in your component.
     *
     * @param string $setting
     * @return $this
     */
    public function setAll($setting)
    {
        foreach ($this->registeredPermissions as $name => $description) {
            $this->set($name, $setting);
        }

        return $this;
    }

    /**
     * Set the provided permission's setting.
     *
     * @throws \Dewdrop\Exception
     * @param string $name
     * @param mixed $setting
     * @return $this
     */
    public function set($name, $setting)
    {
        if (!array_key_exists($name, $this->registeredPermissions)) {
            throw new Exception("Could not find permission with name '{$name}'");
        }

        $this->settings[$name] = $setting;

        return $this;
    }

    /**
     * Set the provided permission's setting and lock it so it cannot be modified
     * by users.
     *
     * @throws \Dewdrop\Exception
     * @param string $name
     * @param mixed $setting
     * @return $this
     */
    public function lock($name, $setting)
    {
        if (!array_key_exists($name, $this->registeredPermissions)) {
            throw new Exception("Could not find permission with name '{$name}'");
        }

        $this->lockedSettings[] = $name;
        $this->set($name, $setting);

        return $this;
    }

    /**
     * Get the role values (rather than the clumsy SfRole objects) for the
     * provided user object.
     *
     * @param SfSecurityUserInterface $user
     * @return array
     */
    protected function getUserRoleValues(SfSecurityUserInterface $user)
    {
        $roles = [];

        foreach ($user->getRoles() as $role) {
            if ($role instanceof SfRole) {
                $role = $role->getRole();
            }

            $roles[] = $role;
        }

        return $roles;
    }
}
