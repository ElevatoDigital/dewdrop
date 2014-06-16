<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Fields;
use Dewdrop\Fields\GroupedFields\Group;

/**
 * Break a set of fields into multiple groups.  Components uses a
 * \Dewdrop\Fields\GroupedFields object that are not aware of the specific
 * APIs it provides can use it just like a plain \Dewdrop\Fields object.
 * However, for those components (e.g. the BootstrapForm view helper) that
 * support the grouping features, they can use the groups to render their
 * fields differently.
 */
class GroupedFields extends Fields
{
    /**
     * The groups added to this object.
     *
     * @see addGroup()
     * @var array
     */
    private $groups = array();

    /**
     * Remove a field from this collection.  This will remove it from the
     * core collection and whatever group it is a member of.
     *
     * @param string $id
     * @return GroupedFields
     */
    public function remove($id)
    {
        parent::remove($id);

        foreach ($this->groups as $group) {
            if ($group->has($id)) {
                $group->remove($id);
            }
        }

        return $this;
    }

    /**
     * Add a new group to this collection with the supplied ID.
     *
     * @param string $id
     * @return \Dewdrop\Fields\GroupedFields\Group
     */
    public function addGroup($id)
    {
        $this->groups[$id] = new Group($this);

        return $this->groups[$id];
    }

    /**
     * Check to see if a group with the given ID exists.
     *
     * @param string $id
     * @return boolean
     */
    public function hasGroup($id)
    {
        return array_key_exists($id, $this->groups);
    }

    /**
     * Get the group with the specific ID.  You should first check that it
     * exists with hasGroup().
     *
     * @param string $id
     * @return \Dewdrop\Fields\GroupedFields\Group
     */
    public function getGroup($id)
    {
        return $this->groups[$id];
    }

    /**
     * Get all groups.
     *
     * @return array
     */
    public function getGroups()
    {
        return array_values($this->groups);
    }
}
