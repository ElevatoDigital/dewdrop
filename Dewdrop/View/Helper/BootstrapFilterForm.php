<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\SelectFilter;
use HtmlNode\Node;

/**
 * Render a form that allows the user to filter by one or more fields.  The form
 * submits via GET by default, creating a query string that can be copy and pasted
 * to share search results, though this can be overwritten, if you'd like to use
 * filters in a different context.
 */
class BootstrapFilterForm extends AbstractHelper
{
    /**
     * When calling this helper, there are three types of arguments we accept:
     *
     * 1) No arguments at all, will just return a reference to the helper instance.
     *
     * 2) A CrudInterface admin component, which will provide some sane defaults
     *    when working with a typical admin component.
     *
     * 3) A set of fields, a SelectFilter and a title.
     *
     * @return string
     */
    public function direct()
    {
        $args = func_get_args();

        if (!count($args)) {
            return $this;
        } elseif (isset($args[0]) && $args[0] instanceof CrudInterface) {
            return $this->directWithComponent($args[0]);
        } else {
            return call_user_func_array(array($this, 'directWithArgs'), $args);
        }
    }

    /**
     * Render a filter form for the provided CrudInterface admin component.
     *
     * @param CrudInterface $component
     * @return string
     */
    public function directWithComponent(CrudInterface $component)
    {
        return $this->directWithArgs(
            $component->getFields()->getFilterableFields($component->getFieldGroupsFilter()),
            $component->getListing()->getSelectModifierByName('SelectFilter'),
            $component->getPrimaryModel()->getPluralTitle()
        );
    }

    /**
     * Render a filter form using the supplied Fields, SelectFilter and title.
     * By default, this form will use GET so that it geneates a query string
     * that can be used to share search results, but you can use POST if needed
     * for your case.
     *
     * @param Fields $fields
     * @param SelectFilter $selectFilter
     * @param string $title
     * @param string $method
     * @return string
     */
    public function directWithArgs(Fields $fields, SelectFilter $selectFilter, $title, $method = 'GET')
    {
        return Node::create('form')
            ->addClass('filter-form')
            ->setAttribute('data-prefix', $selectFilter->getPrefix())
            ->setAttribute('action', '')
            ->setAttribute('method', $method)
            ->setHtml(
                $this->partial(
                    'bootstrap-filter-form.phtml',
                    ['controls' => $this->inline($fields, $selectFilter, $title, $method, true)]
                )
            );
    }

    /**
     * Render the filter controls inside a form tag that is rendered elsewhere.
     * No form HTML tags or buttons will be rendered by this method, only the
     * filter controls themselves.
     *
     * By default, this form will use GET so that it geneates a query string
     * that can be used to share search results, but you can use POST if needed
     * for your case.
     *
     * @param Fields $fields
     * @param SelectFilter $selectFilter
     * @param string $title
     * @param string $method
     * @param boolean $buttons
     * @return string
     */
    public function inline(Fields $fields, SelectFilter $selectFilter, $title, $method = 'GET', $buttons = false)
    {
        $this->view->headScript()->appendFile($this->view->bowerUrl('/dewdrop/www/js/filter/main.js'));
        $this->view->headLink()->appendStylesheet($this->view->bowerUrl('/dewdrop/www/css/filter.css'));

        return $this->partial(
            'bootstrap-filter-controls.phtml',
            array(
                'fields'      => $fields->getFilterableFields(),
                'typeHelper'  => $selectFilter->getFilterTypeHelper(),
                'values'      => $selectFilter->getSelectModifier()->getCurrentFilters(),
                'defaultVars' => $selectFilter->getDefaultVarsHelper(),
                'title'       => $title,
                'method'      => $method,
                'paramPrefix' => $selectFilter->getPrefix(),
                'showButtons' => $buttons
            )
        );
    }

    /**
     * Render the filter form, wrapped in the panel used by typical admin CRUD
     * components, rather than as a standalone form.
     *
     * @param CrudInterface $component
     * @return string
     */
    public function adminPanel(CrudInterface $component)
    {
        return $this->partial(
            'bootstrap-filter-form-admin-panel.phtml',
            array(
                'component' => $component,
                'form'      => $this->directWithComponent($component)
            )
        );
    }
}
