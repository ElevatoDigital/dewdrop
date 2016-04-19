<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page;

use Dewdrop\Exception;
use Dewdrop\Request;
use Dewdrop\Admin\Component\ComponentInterface;
use Dewdrop\Fields\Edit as EditFields;
use Zend\InputFilter\InputFilter;

/**
 * DEPRECATED!  Look at \Dewdrop\Fields\RowEditor instead.
 *
 * An abstract page controller to make the standard edit page workflow easier
 * to implement.  When extending this class, the normal page controller workflow
 * is altered in the following ways:
 *
 * <ul>
 *     <li>Your process method will only be called if the request is a POST</li>
 *     <li>You'll have a \Dewdrop\Fields\Edit object available automatically</li>
 *     <li>You'll have a \Zend\InputFilter\InputFilter object available</li>
 *     <li>The findRowById() method makes it easy to get a row based on the query string</li>
 * </ul>
 *
 * Prior to calling your page's process() method, EditAbstract will do a couple
 * things that are very consistently needed for add/edit pages.  First, it will
 * skip your process() method completely if the request is not a POST, as
 * mentioned above.  Second, if the request _is_ a POST, EditAbstract will
 * automatically pass the data from POST to your fields and input filter
 * objects.
 *
 * That means by the time your process() method is running, you only need to
 * concern yourself with a couple steps:
 *
 * <ol>
 *     <li>Use the input filter to determine if the input from POST was valid.</li>
 *     <li>Apply any post-validation filtering you need (e.g. password hashing)</li>
 *     <li>Save DB rows objects as needed</li>
 *     <li>Set a redirect and/or success message on the supplied response object</li>
 * </ol>
 *
 * The following is a simple example of applying the described tasks:
 *
 * <pre>
 *
 * namespace Admin\MyComponent;
 *
 * use Dewdrop\Admin\Page\EditAbstract;
 *
 * class MyPage extends EditAbstract
 * {
 *     // ... your init method ...
 *
 *     public function process($response)
 *     {
 *         if ($this->inputFilter->isValid()) {
 *             $this->row->save();
 *
 *             $this->response
 *                 ->setSuccessMessage('You saved it!  Wooohoooo!')
 *                 ->setRedirectToAdminPage('Index');
 *         }
 *     }
 *
 *     // ... your render method
 * }
 *
 * </pre>
 *
 * @deprecated
 * @see \Dewdrop\Fields\RowEditor
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
     * A \Zend\InputFilter\InputFilter object that can be used for filtering
     * and validating any input accepted by this page.  This input filter
     * will be passed directly to the \Dewdrop\Fields\Edit object but you
     * can also add your own input objects for non-Field-related input
     * (e.g. credit card security codes, plaintext passwords, etc.) and handle
     * their post-validation processing and filtering independently.
     *
     * @var \Zend\InputFilter\InputFilter
     */
    protected $inputFilter;

    /**
     * We keep a list of fields with errors so that we can check our validation
     * logic while testing.
     *
     * @var array
     */
    private $fieldsWithErrors = array();

    /**
     * Override the PageAbstract contructor so we can add a \Dewdrop\Fields\Edit
     * object before proceeding to init().
     *
     * @param ComponentInterface $component
     * @param Request $request
     * @param string $pageFile The file in which the page class is defined.
     */
    public function __construct(ComponentInterface $component, Request $request, $pageFile)
    {
        parent::__construct($component, $request, $pageFile);

        $this->inputFilter = new InputFilter();
        $this->fields      = new EditFields($this->inputFilter);
    }

    /**
     * Only proceed to process() method if the request is a POST.
     *
     * If the request _is_ a POST, pass the POST data along to the fields
     * object and the input filter.
     *
     * @return boolean
     */
    public function shouldProcess()
    {
        if ($this->request->isPost()) {
            $post = $this->request->getPost();

            $this->fields->setValues($post);
            $this->inputFilter->setData($post);

            return true;
        }

        return false;
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

        /* @var $model \Dewdrop\Db\Table */
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

    /**
     * Retrieve error messages from input filter.
     *
     * If the specific input object generating the message is tied to a Field,
     * we will prefix the field's label to the error message to make it easier
     * to understand.
     *
     * @return array
     */
    public function getErrorsFromInputFilter()
    {
        $errors = array();

        foreach ($this->inputFilter->getInvalidInput() as $id => $error) {
            foreach ($error->getMessages() as $message) {
                if (!$this->fields->has($id)) {
                    $errors[] = $message;
                } else {
                    $field = $this->fields->get($id);
                    $this->fieldsWithErrors[] = $field->getControlName();
                    $errors[] = $field->getLabel() . ': ' . $message;
                }
            }
        }

        return $errors;
    }

    /**
     * Check to see whether the supplied field control name is associated
     * with any validation errors.
     *
     * @param string $name
     */
    public function fieldHasError($name)
    {
        if (!$this->fields->has($name)) {
            throw new Exception("Checking for errors on unknown field \"{$name}\"");
        }

        return in_array($name, $this->fieldsWithErrors);
    }

    /**
     * Get a reference to the page's input filter.  Primarily used for testing.
     *
     * @return \Zend\InputFilter\InputFilter
     */
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    /**
     * Get a reference to the page's fields.  Primarily used for testing.
     *
     * @return \Dewdrop\Fields\Edit
     */
    public function getFields()
    {
        return $this->fields;
    }
}
