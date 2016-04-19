<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Admin\Component\ComponentInterface;
use Dewdrop\Admin\Component\CrudInterface;

/**
 * This helper renders the primary nav bar that's used on typical Dewdrop CRUD
 * components.  It includes a button for creating a new item, exporting as CSV,
 * adjusting which columns are visible, etc.
 */
class AdminComponentNav extends AbstractHelper
{
    /**
     * Render the nav for the provided component.
     *
     * @param ComponentInterface $component
     * @return string
     */
    public function direct(ComponentInterface $component, array $options = [])
    {
        if ($component instanceof CrudInterface) {
            $singularTitle = $component->getPrimaryModel()->getSingularTitle();
        } else {
            $singularTitle = $component->getTitle();
        }

        if ($component instanceof CrudInterface) {
            $pluralTitle = $component->getPrimaryModel()->getPluralTitle();
        } else {
            $pluralTitle = $component->getTitle();
        }

        if (!isset($options['createUrl'])) {
            $options['createUrl'] = null;
        }

        return $this->partial(
            'admin-component-nav.phtml',
            array(
                'permissions'   => $component->getPermissions(),
                'singularTitle' => $singularTitle,
                'pluralTitle'   => $pluralTitle,
                'createUrl'     => $options['createUrl']
            )
        );
    }
}
