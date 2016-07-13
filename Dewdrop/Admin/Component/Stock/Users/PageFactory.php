<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component\Stock\Users;

use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\PageFactory\Crud as CrudPageFactory;

/**
 * Page factory for stock admin users component
 */
class PageFactory extends CrudPageFactory
{
    /**
     * Constructor
     *
     * @param CrudInterface $component
     */
    public function __construct(CrudInterface $component)
    {
        parent::__construct($component);

        $this->pageClassMap['change-password'] = '\Dewdrop\Admin\Component\Stock\Users\ChangePassword';
        $this->pageClassMap['edit']            = '\Dewdrop\Admin\Component\Stock\Users\Edit';
    }
}
