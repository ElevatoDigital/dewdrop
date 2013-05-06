<?php

namespace Dewdrop\Fields;

use Dewdrop\Db\Adapter;
use Dewdrop\Test\BaseTestCase;

class OptionPairsTest extends BaseTestCase
{
    private $pairs;

    private $db;

    public function setUp()
    {
        $wpdb = new \wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

        $this->db    = new Adapter($wpdb);
        $this->pairs = new OptionPairs($this->db);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testUseOfUnknownTableNameWillThrowException()
    {
        $this->pairs
            ->setTableName('fafafafa')
            ->getStmt();
    }

    public function testWillAutoDetectTitleAndValueColumns()
    {
        $this->pairs
            ->setTableName('dewdrop_test_fruits');

        $stmt = (string) $this->pairs->getStmt();

        $this->assertContains('`dewdrop_test_fruits`.`dewdrop_test_fruit_id`', $stmt);
        $this->assertContains('`dewdrop_test_fruits`.`name`', $stmt);
        $this->assertContains('ORDER BY `dewdrop_test_fruits`.`name`', $stmt);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testWillThrowExceptionIfNoSuitableTitleColumnIsAvailabile()
    {
        $pairs = $this->getMock(
            '\Dewdrop\Fields\OptionPairs',
            array('loadTableMetadata'),
            array($this->db)
        );

        $pairs
            ->expects($this->once())
            ->method('loadTableMetadata')
            ->will($this->returnValue(require __DIR__ . '/option-pairs/no_title_column.php'));

        $pairs
            ->setTableName('fafafafa')
            ->getStmt();
    }

    public function testWillOrderBySortIndexColumnIfAvailable()
    {
        $pairs = $this->getMock(
            '\Dewdrop\Fields\OptionPairs',
            array('loadTableMetadata'),
            array($this->db)
        );

        $pairs
            ->expects($this->once())
            ->method('loadTableMetadata')
            ->will($this->returnValue(require __DIR__ . '/option-pairs/will_use_sort_index.php'));

        $pairs
            ->setTableName('fafafafa');

        $this->assertContains(
            "ORDER BY `fafafafa`.`sort_index` ASC, `fafafafa`.`dewdrop_test_fruit_id` ASC",
            (string) $pairs->getStmt()
        );
    }

    public function testWillOrderBySortOrderColumnIfAvailable()
    {
        $pairs = $this->getMock(
            '\Dewdrop\Fields\OptionPairs',
            array('loadTableMetadata'),
            array($this->db)
        );

        $pairs
            ->expects($this->once())
            ->method('loadTableMetadata')
            ->will($this->returnValue(require __DIR__ . '/option-pairs/will_use_sort_order.php'));

        $pairs
            ->setTableName('fafafafa');

        $this->assertContains(
            "ORDER BY `fafafafa`.`sort_order` ASC, `fafafafa`.`dewdrop_test_fruit_id` ASC",
            (string) $pairs->getStmt()
        );
    }
}
