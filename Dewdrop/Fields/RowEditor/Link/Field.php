<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\RowEditor\Link;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Db\Row;
use Dewdrop\Db\Table;
use Dewdrop\Fields\Exception;
use Dewdrop\Fields\RowEditor;

/**
 * Provide a row that can be associated with all the fields from a model
 * in a RowEditor.  This particular linker uses a field object to get the
 * look-up value.  This can be useful if you have a primary row linked in
 * the row editor by query string and you then need to pull in a second,
 * related row based upon a foreign key in the first.
 *
 * For example, say you were writing a order management component.  Your
 * edit page would pull up the order using the "order_id" query string
 * variable and then pull up the associated address rows using the order
 * row's shipping_address_id and billing_address_id fields.  To pull
 * this off, you'd set up your RowEditor like this:
 *
 * <pre>
 * $this->rowEditor
 *     ->linkByQueryString('orders', 'order_id')
 *     ->linkByField('shipping_address', $orderModel->field('shipping_address_id'))
 *     ->linkByField('billing_address', $orderModel->field('billing_address_id'));
 * </pre>
 *
 * Note that because this linker requires the look-up field have its own row
 * association, it requires a \Dewdrop\Db\Field, not just a
 * \Dewdrop\Field\FieldInterface.
 */
class Field implements LinkInterface
{
    /**
     * The RowEditor this link is associated with.  Used to get the field's row,
     * if needed.
     *
     * @var RowEditor
     */
    private $rowEditor;

    /**
     * The field that will be used to get the look-up value.
     *
     * @var DbField
     */
    private $field;

    /**
     * Provide a field that can be used when looking up the primary key value
     * for the generated row object.
     *
     * @param RowEditor $rowEditor
     * @param DbField $field
     */
    public function __construct(RowEditor $rowEditor, DbField $field)
    {
        $this->rowEditor = $rowEditor;
        $this->field     = $field;
    }

    /**
     * Provide a row that can be linked to all the fields from the supplied
     * table.  If the field has a value, we'll look up the row using the table's
     * find() method.  Otherwise, we'll create a new row.  Note that an exception
     * is throw if you attempt to use this linker with a field that doesn't
     * itself have a row associated with it already.  Often you'll link to the
     * first row using a \Dewdrop\Fields\RowEditor\Link\QueryString rule and
     * then string Field linker on after that.
     *
     * @throws \Dewdrop\Fields\Exception
     * @param Table $table
     * @return \Dewdrop\Db\Row
     */
    public function link(Table $table)
    {
        if (!$this->field->hasRow()) {
            $this->field->setRow($this->rowEditor->getRow($this->field->getGroupName()));
        }

        $value = $this->field->getValue();

        if ($value) {
            $row = $table->find($value);
        } else {
            $row = $table->createRow();
        }

        return $row;
    }

    /**
     * Populate the primary key value from the supplied row (already saved in
     * the RowEditor) up to the linked field in this object.  For example, say
     * you'd configured a link like this in your RowEditor:
     *
     * <pre>
     * $rowEditor->linkByField('addresses', $this->orderModel->field('address_id'));
     * </pre>
     *
     * Then, when this method was called, you'd get a row from the addresses
     * table that had already been saved by the row editor.  And, using that row,
     * this method would set the value of the orderModel's address_id field
     * to the primary key of the addresses row.
     *
     * @param Row $row
     * @return \Dewdrop\Fields\RowEditor\Link\Field
     */
    public function populateValueFromSavedRow(Row $row)
    {
        $references = $this->field->getTable()->getMetadata('references');

        foreach ($references as $foreignKey => $referencedColumnAndTable) {
            if ($foreignKey === $this->field->getName()) {
                $referencedColumn = $referencedColumnAndTable['column'];

                $this->field->setValue($row[$referencedColumn]);
            }
        }

        return $this;
    }
}
