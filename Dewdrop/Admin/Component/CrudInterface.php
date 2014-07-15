<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component;

/**
 * Fulfilling this interface in your Component class has several benefits:
 *
 * 1) Combined with a \Dewdrop\PageFactory\Crud object, many admin pages
 *    can be automatically handled for you without writing any additional
 *    code.
 *
 * 2) Any pages you do write yourself will be cleaner.  Having these core
 *    objects for your component defined centrally means that other pages
 *    can re-use them easily and avoid errors introduced by repetitive code.
 *
 * 3) Other parts of Dewdrop (not just pages in your admin component) could
 *    use the resources provided by this interface to provide additional
 *    services.  The most important example of this is automated testing.
 *
 * Providing a nice admin user experience can be very difficult.  Your users
 * want tools that are just as capable and pleasant as the tools that you
 * enjoy using for your own work.  But providing that level of quality and
 * attention to detail is very challenging on the budget and timeline typically
 * available for admin/backend work.
 *
 * This interface and the related tools make that task much more manageable
 * by turning what was a very detail-oriented task into a declaractive,
 * configuration-driven task.
 */
interface CrudInterface
{
    /**
     * Get the primary model that is used by this component.  This model will
     * be used to provide page and button titles.  By default, its primary key
     * will also be used to filter the listing when needed (e.g. when viewing
     * a single item rather than the full listing).
     *
     * @return \Dewdrop\Db\Table
     */
    public function getPrimaryModel();

    /**
     * Get a \Dewdrop\Fields\Listing object that allows the component to
     * retrieve records for viewing.  The Listing handles applying user sorts
     * and filters.
     *
     * @return \Dewdrop\Fields\Listing
     */
    public function getListing();

    /**
     * Get the \Dewdrop\Fields object that defines what fields are available to
     * this component, what capabilities that each have, and how they should
     * interact with various \Dewdrop\Fields\Helper objects.
     *
     * @return \Dewdrop\Fields
     */
    public function getFields();

    /**
     * Get a \Dewdrop\Fields\Filter\Groups object to allow the user to sort
     * and group their fields.
     *
     * @return \Dewdrop\Fields\Filter\Groups
     */
    public function getFieldGroupsFilter();

    /**
     * Get a \Dewdrop\Fields\Filter\Visibility object that allows the user
     * to select which fields should be visible on listings.
     *
     * @return \Dewdrop\Fields\Filter\Visibility
     */
    public function getVisibilityFilter();

    /**
     * Get the \Dewdrop\Fields\RowEditor object that will assist with the
     * editing of items in this component.
     *
     * @return \Dewdrop\Fields\RowEditor
     */
    public function getRowEditor();
}
