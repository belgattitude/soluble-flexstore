<?php

/*
 * soluble-flexstore library
 *
 * @author    Vanvelthem Sébastien
 * @link      https://github.com/belgattitude/soluble-flexstore
 * @copyright Copyright (c) 2016-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-flexstore/blob/master/LICENSE.md
 *
 */

namespace SolubleTest\FlexStore\Source\Zend;

use PHPUnit\Framework\TestCase;
use Soluble\FlexStore\Options;
use Soluble\FlexStore\Source\Zend\SqlSource;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-10-14 at 12:05:43.
 */
class SqlSourceTest extends TestCase
{
    /**
     * @var SqlSource
     */
    protected $source;

    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->adapter = \SolubleTestFactories::getDbAdapter();
        $select = new \Zend\Db\Sql\Select();
        $select->from('user');

        $this->source = new SqlSource($this->adapter, $select);
    }

    public function testGetMetadata()
    {
        $metadata = $this->source->getMetadataReader();
        self::assertInstanceOf('\Soluble\Metadata\Reader\AbstractMetadataReader', $metadata);
    }

    public function testGetColumnModel()
    {
        $columnModel = $this->source->getColumnModel();
        self::assertInstanceOf('\Soluble\FlexStore\Column\ColumnModel', $columnModel);
        $columns = $columnModel->getColumns();
        self::assertInstanceOf('ArrayObject', $columns);
        foreach ($columns as $column) {
            self::assertFalse($column->isVirtual());
        }
    }

    public function testGetMetadatareader()
    {
        $select = new Select();
        $select->from(['p' => 'product']);
        $source = new SqlSource($this->adapter, $select);
        $mr = $source->getMetadataReader();
        self::assertInstanceOf('Soluble\Metadata\Reader\AbstractMetadataReader', $mr);
    }

    public function testIdentifier()
    {
        $select = new Select();
        $select->from(['p' => 'product']);

        $source = new SqlSource($this->adapter, $select);
        self::assertNull($source->getIdentifier());
        $source->setIdentifier('product_id');
        self::assertEquals('product_id', $source->getIdentifier());
    }

    public function testCalcFoundRowsAndWithZeroLimit()
    {
        $select = new Select();
        $select->from(['p' => 'product']);

        $options = new Options();
        $options->setLimit(0, 0);

        $source = new SqlSource($this->adapter, $select);

        $data = $source->getData($options);
        self::assertEquals(0, $data->count());
        self::assertEquals(0, $data->getTotalRows());

        // Edge, test if SQL_CALC_FOUND_ROWS was really injected
        $query = $source->__toString();
        self::assertNotContains('SQL_CALC_FOUND_ROWS', $query);
        self::assertStringContainsStringIgnoringCase('LIMIT 0 OFFSET 0', $query);
    }

    public function testCalcFoundRowsAndOptions()
    {
        $select = new Select();
        $select->from(['p' => 'product']);

        $options = new Options();
        $options->setLimit(2, 0);

        $source = new SqlSource($this->adapter, $select);
        $data = $source->getData($options);
        self::assertEquals(2, $data->count());
        self::assertGreaterThan(2, $data->getTotalRows());

        // Edge, test if SQL_CALC_FOUND_ROWS was really injected
        $query = $source->__toString();
        self::assertStringContainsStringIgnoringCase('SQL_CALC_FOUND_ROWS', $query);
        self::assertStringContainsStringIgnoringCase('LIMIT 2 OFFSET 0', $query);

        // Second edge case, when the query already contains
        // a quantifier : it should not be overridden

        $select = new Select();
        $select->from(['p' => 'product']);
        $select->quantifier(new Expression('SQL_NO_CACHE'));

        $source = new SqlSource($this->adapter, $select);
        $source->getData($options);
        $query = $source->__toString();
        self::assertStringContainsStringIgnoringCase('SQL_CALC_FOUND_ROWS SQL_NO_CACHE', $query);

        // Third edge case, when the query already contains
        // a quantifier but in string version : it should not be overridden

        $select = new Select();
        $select->from(['p' => 'product']);
        $select->quantifier('SQL_NO_CACHE');

        $source = new SqlSource($this->adapter, $select);
        $source->getData($options);
        $query = $source->getQueryString();
        self::assertStringContainsStringIgnoringCase('SQL_CALC_FOUND_ROWS SQL_NO_CACHE', $query);
    }

    public function testCustomQuery()
    {
        $select = new Select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], 'ppl.product_id = p.product_id', [], Select::JOIN_LEFT)
                ->limit(100);

        $select->columns([
           'product_id' => new Expression('p.product_id'),
           'brand_id' => new Expression('p.brand_id'),
           'reference' => new Expression('p.reference'),
           'description' => new Expression('p.description'),
           'volume' => new Expression('p.volume'),
           'weight' => new Expression('p.weight'),
           'barcode_ean13' => new Expression('1234567890123'),
           'created_at' => new Expression('NOW()'),
           'price' => new Expression('ppl.price'),
           'discount_1' => new Expression('ppl.discount_1'),
           'pricelist.promo_start_at' => new Expression('ppl.promo_start_at'),
           'promo_end_at' => new Expression('cast(NOW() as date)')
        ], false);

        $source = new SqlSource($this->adapter, $select);
        $data = $source->getData();
        self::assertTrue($data->count() > 0);
        //var_dump($data->toArray());

        //$columnModel = $source->getColumnModel();
        //$cm = $columnModel->getColumns();
        //var_dump($cm);
        //var_dump($select->getRawState());
        //die();
    }

    public function testGetData()
    {
        $source1 = $this->getNewSource();
        $source2 = $this->getNewSource();

        $data = $source1->getData();
        $this->isInstanceOf('Soluble\FlexStore\ResultSet\ResultSet');
        $d = $data->toArray();
        self::assertInternalType('array', $d);
        self::assertArrayHasKey('user_id', $d[0]);
        self::assertArrayHasKey('email', $d[0]);

        $options = new Options();
        $options->setLimit(10, 0);

        $data2 = $source2->getData($options);
        $d2 = $data2->toArray();

        self::assertInternalType('array', $d2);

        self::assertArrayHasKey('user_id', $d2[0]);
        self::assertArrayHasKey('email', $d2[0]);
        self::assertEquals($d[0], $d2[0]);
    }

    public function testGetDataThrowsEmptyQueryException()
    {
        $this->expectException('Soluble\FlexStore\Exception\EmptyQueryException');

        $source = new SqlSource($this->adapter, $select = new Select());

        $source->getData();
    }

    public function testGetQueryString()
    {
        $data = $this->source->getData();
        $sql_string = $this->source->getQueryString();
        self::assertInternalType('string', $sql_string);
        self::assertRegExp('/^select/', strtolower(trim($sql_string)));
    }

    public function testGetQueryStringThrowsInvalidUsageException()
    {
        $this->expectException('Soluble\FlexStore\Exception\InvalidUsageException');
        $sql_string = $this->source->getQueryString();
        self::assertInternalType('string', $sql_string);
        self::assertRegExp('/^select/', strtolower(trim($sql_string)));
    }

    /**
     * @return \Soluble\FlexStore\Source\Zend\SqlSource
     */
    protected function getNewSource()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('user');

        return new SqlSource($this->adapter, $select);
    }
}
