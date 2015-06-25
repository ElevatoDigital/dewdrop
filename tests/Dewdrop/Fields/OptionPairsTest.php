<?php

namespace Dewdrop\Fields;

use Dewdrop\Db\Adapter;
use Dewdrop\Test\BaseTestCase;

class OptionPairsTest extends BaseTestCase
{
    private $pairs;

    private $db;

    private $idChar;

    public function setUp()
    {
        $this->db     = $GLOBALS['dewdrop_pimple']['db'];
        $this->pairs  = new OptionPairs($this->db);
        $this->idChar = $this->db->getQuoteIdentifierSymbol();
    }

    private function withMockMetadata($file)
    {
        $pairs = $this->getMock(
            '\Dewdrop\Fields\OptionPairs',
            array('loadTableMetadata'),
            array($this->db)
        );

        $pairs
            ->expects($this->once())
            ->method('loadTableMetadata')
            ->will($this->returnValue(require __DIR__ . '/option-pairs/' . $file));

        $pairs
            ->setTableName('fafafafa');

        return $pairs;
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

        $this->assertContains("{$this->idChar}dewdrop_test_fruits{$this->idChar}.{$this->idChar}dewdrop_test_fruit_id{$this->idChar}", $stmt);
        $this->assertContains("{$this->idChar}dewdrop_test_fruits{$this->idChar}.{$this->idChar}name{$this->idChar}", $stmt);
        $this->assertContains("ORDER BY {$this->idChar}dewdrop_test_fruits{$this->idChar}.{$this->idChar}name{$this->idChar}", $stmt);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testWillThrowExceptionIfNoSuitableTitleColumnIsAvailabile()
    {
        $this->withMockMetadata('no_title_column.php')->getStmt();
    }

    public function testWillOrderBySortIndexColumnIfAvailable()
    {
        $pairs = $this->withMockMetadata('will_use_sort_index.php');

        $this->assertContains(
            "ORDER BY {$this->idChar}fafafafa{$this->idChar}.{$this->idChar}sort_index{$this->idChar} ASC, {$this->idChar}fafafafa{$this->idChar}.{$this->idChar}dewdrop_test_fruit_id{$this->idChar} ASC",
            (string) $pairs->getStmt()
        );
    }

    public function testWillOrderBySortOrderColumnIfAvailable()
    {
        $pairs = $this->withMockMetadata('will_use_sort_order.php');

        $this->assertContains(
            "ORDER BY {$this->idChar}fafafafa{$this->idChar}.{$this->idChar}sort_order{$this->idChar} ASC, {$this->idChar}fafafafa{$this->idChar}.{$this->idChar}dewdrop_test_fruit_id{$this->idChar} ASC",
            (string) $pairs->getStmt()
        );
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testSettingUnknownOptionThrowsException()
    {
        $this->pairs->setOptions(
            array(
                'fafafafa' => null
            )
        );
    }

    public function testCanSetMultipleOptionsToOverrideMetadata()
    {
        $this->pairs->setOptions(
            array(
                'tableName'   => 'dewdrop_test_fruits',
                'titleColumn' => 'titleColumn',
                'valueColumn' => 'valueColumn'
            )
        );

        $stmt = (string) $this->pairs->getStmt();

        $this->assertContains("{$this->idChar}dewdrop_test_fruits{$this->idChar}.{$this->idChar}titleColumn{$this->idChar} AS {$this->idChar}title{$this->idChar}", $stmt);
        $this->assertContains("{$this->idChar}dewdrop_test_fruits{$this->idChar}.{$this->idChar}valueColumn{$this->idChar} AS {$this->idChar}value{$this->idChar}", $stmt);
    }

    public function testCanOverrideSqlWithCustomSelectObject()
    {
        $stmt = $this->db->select();

        $stmt
            ->from('dewdrop_test_animals', array('dewdrop_test_animal_id', 'name'))
            ->order('name');

        $this->pairs->setStmt($stmt);

        $this->assertEquals(
            (string) $stmt,
            (string) $this->pairs->getStmt()
        );
    }

    public function testWillFilterByActiveFieldIfAvailable()
    {
        $pairs = $this->withMockMetadata('filter_by_active.php');

        $this->assertContains(
            "WHERE ({$this->idChar}fafafafa{$this->idChar}.{$this->idChar}active{$this->idChar} = true)",
            (string) $pairs->getStmt()
        );
    }

    public function testWillFilterByDeletedFieldIfAvailable()
    {
        $pairs = $this->withMockMetadata('filter_by_deleted.php');

        $this->assertContains(
            "WHERE ({$this->idChar}fafafafa{$this->idChar}.{$this->idChar}deleted{$this->idChar} = false)",
            (string) $pairs->getStmt()
        );
    }

    public function testWillGetReferenceToSameStmtObjectWithMultipleGetStmtCalls()
    {
        $this->pairs->setTableName('dewdrop_test_fruits');

        $this->assertEquals(
            spl_object_hash($this->pairs->getStmt()),
            spl_object_hash($this->pairs->getStmt())
        );
    }

    public function testWillUseColumnNamedTitleIfAvailable()
    {
        $pairs = $this->withMockMetadata('column_named_title.php');

        $this->assertContains("{$this->idChar}fafafafa{$this->idChar}.{$this->idChar}title{$this->idChar}", (string) $pairs->getStmt());
    }

    public function testWillFallBackToFirstCharColumnForTitle()
    {
        $pairs = $this->withMockMetadata('first_char_as_title.php');

        $this->assertContains(
            "{$this->idChar}fafafafa{$this->idChar}.{$this->idChar}just_a_char{$this->idChar} AS {$this->idChar}title{$this->idChar}",
            (string) $pairs->getStmt()
        );
    }

    public function testCanUseExprObjectAsTitleColumn()
    {
        $this->pairs->setOptions(
            array(
                'tableName'   => 'dewdrop_test_fruits',
                'titleColumn' => new \Dewdrop\Db\Expr('UPPER(name)')
            )
        );

        $this->assertContains(
            "UPPER(name) AS {$this->idChar}title{$this->idChar}",
            (string) $this->pairs->getStmt()
        );
    }

    public function testWillFetchPairsWithKeyValuesAndValueTitles()
    {
        $options = $this->pairs->setTableName('dewdrop_test_fruits')->fetch();

        $valuesAreInts    = true;
        $titlesAreStrings = true;

        foreach ($options as $value => $title) {
            if (!is_int($value)) {
                $valuesAreInts = false;
            }

            if (!is_string($title)) {
                $titlesAreStrings = false;
            }
        }

        $this->assertTrue($valuesAreInts);
        $this->assertTrue($titlesAreStrings);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLackOfSuitableValueColumnThrowsException()
    {
        $this->withMockMetadata('no_value_column.php')->getStmt();
    }
}
