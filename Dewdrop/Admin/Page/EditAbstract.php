<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page;

use Dewdrop\Admin\ComponentAbstract;
use Dewdrop\Fields\Edit as EditFields;

/**
 * An abstract page controller to make the standard edit page workflow easier
 * to implement.  When extending this class, the normal page controller workflow
 * is altered in the following ways:
 *
 * <ul>
 *     <li>Your process method will only be called if the request is a POST</li>
 *     <li>You'll have a \Dewdrop\Fields\Edit object available automatically</li>
 *     <li>The findRowById() method makes it easy to get a row based on the query string</li>
 * </ul>
 */
abstract class EditAbstract extends PageAbstract
{
    /**
     * The \Dewdrop\Fields\Edit object used to manage and validate DB fields
     *
     * @var \Dewdrop\Fields\Edit
     */
    protected $fields;

    /**
     * Override the PageAbstract contructor so we can add a \Dewdrop\Fields\Edit
     * object before proceeding to init().
     *
     * @param ComponentAbstract $component
     * @param string $pageFile The file in which the page class is defined.
     */
    public function __construct(ComponentAbstract $component, $pageFile)
    {
        parent::__construct($component, $pageFile);

        $this->fields = new EditFields();
    }

    /**
     * Only proceed to process() method if the request is a POST.
     *
     * @return boolean
     */
    public function shouldProcess()
    {
        return $this->request->isPost();
    }

    /**
     * Instantiate the provided model class and return a row for editing.
     *
     * If the primary value(s) for the supplied model class are present in the
     * query string, the matching row will be queried and returned.  Otherwise,
     * a new row will be returned.
     *
     * This method will also use your supplied model class to generate a default
     * title for the page in the style of the WP admin.  For a new row, it will
     * be "Add New {$model->getSingularTitle()}" and for an existing row
     * "Edit {$model->getSingularTitle()}".  If you'd like to override this
     * default, you can do so in your render method by reassigning the "title"
     * variable on your view.
     *
     * @param string $modelClass
     * @return \Dewdrop\Db\Row
     */
    public function findRowById($modelClass)
    {
        if (false === strpos($modelClass, '\\')) {
            $modelClass = '\Model\\' . $modelClass;
        }

        $model = new $modelClass($this->component->getDb());
        $pkey  = $model->getPrimaryKey();
        $query = $this->request->getQuery();
        $id    = array();

        foreach ($pkey as $column) {
            if (isset($query[$column]) && $query[$column]) {
                $id[] = $query[$column];
            }
        }

        if (!count($id)) {
            $this->view->title = "Add New {$model->getSingularTitle()}";

            return $model->createRow();
        } else {
            $this->view->title = "Edit {$model->getSingularTitle()}";

            return call_user_func_array(
                array($model, 'find'),
                $id
            );
        }
    }
}
