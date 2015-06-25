<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\GroupedFields;

use Dewdrop\Fields;
use Dewdrop\Fields\GroupedFields;
use Dewdrop\Fields\UserInterface;

/**
 * A group of fields in a \Dewdrop\Fields\GroupedFields collection.  Each
 * group has a title and a number of fields.  It is a superset of the
 * \Dewdrop\Fields API, so you can still call getVisibleFields(),
 * getEditableFields(), etc. on a group.  Adding and removing fields from
 * a group will also change the underlying \Dewdrop\Fields\GroupedFields
 * object.
 */
class Group extends Fields
{
    /**
     * The \Dewdrop\Fields\GroupedFields object containing this group.
     *
     * @var \Dewdrop\Fields\GroupedFields
     */
    private $groupedFields;

    /**
     * Supply the GroupedFields object that contains this group.
     *
     * @param GroupedFields $groupedFields
     * @param UserInterface $user
     */
    public function __construct(GroupedFields $groupedFields, UserInterface $user = null)
    {
        $this->groupedFields = $groupedFields;

        parent::__construct([], $user);
    }

    /**
     * Add a field to this group and the underlying GroupedFields object.
     *
     * @param mixed $field
     * @param string $modelName
     * @return FieldInterface
     */
    public function add($field, $modelName = null)
    {
        $this->groupedFields->add($field, $modelName);

        return parent::add($field, $modelName);
    }

    /**
     * Remove a field from this group and the GroupedFields container.
     *
     * @param string $id
     * @return Group
     */
    public function remove($id)
    {
        // This check ensures we don't enter an infinite loop because the set will try to remove from the group as well
        if ($this->groupedFields->has($id)) {
            $this->groupedFields->remove($id);
        }

        return parent::remove($id);
    }

    /**
     * Set the title for this group.  This will show up when the group is
     * display (e.g. in a UI tab).
     *
     * @param string $title
     * @return Group
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the group's title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
