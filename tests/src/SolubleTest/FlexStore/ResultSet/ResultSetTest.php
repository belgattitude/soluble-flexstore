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

namespace SolubleTest\FlexStore\ResultSet;

use PHPUnit\Framework\TestCase;
use Soluble\FlexStore\ResultSet\ResultSet;
use Soluble\FlexStore\FlexStore;
use Soluble\FlexStore\Store\StoreInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Soluble\FlexStore\Source\Zend\SqlSource;

class ResultSetTest extends TestCase
{
    /**
     * @var ResultSet
     */
    protected $resultset;

    /**
     * @var StoreInterface
     */
    protected $store;

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->adapter = \SolubleTestFactories::getDbAdapter();
        $select = new Select();
        $select->from('product_brand');

        $this->store = $this->getStore($select);

        $this->resultset = $this->store->getData();
    }

    /**
     * @param Select|null $select
     *
     * @return FlexStore
     */
    protected function getStore(Select $select = null)
    {
        return new FlexStore(new SqlSource($this->adapter, $select));
    }

    /**
     * @covers \Soluble\FlexStore\ResultSet\AbstractResultSet::toArray
     */
    public function testGetArray()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand');

        $store = $this->getStore($select);

        $resultset = $this->store->getData();
        $arr = $resultset->toArray();
        self::assertInternalType('array', $arr);
    }

    public function testResultSetThrowsRuntimeException()
    {
        $this->expectException('Soluble\FlexStore\ResultSet\Exception\RuntimeException');
        $adapter = \SolubleTestFactories::getDbAdapter();
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand')->limit(10);
        $sql = new \Zend\Db\Sql\Sql($adapter);
        $sql_string = $sql->getSqlStringForSqlObject($select);
        $r = $adapter->query($sql_string, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        $rs = new ResultSet($r);
        $rs->getColumnModel();
    }

    public function testGetSource()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand')->limit(1);

        $store = $this->getStore($select);
        $resultset = $store->getData();
        self::assertInstanceOf(\Soluble\FlexStore\Source\AbstractSource::class, $resultset->getSource());
    }

    public function testGetFieldCount()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand')->limit(1)->columns(['reference']);
        $store = $this->getStore($select);
        $resultset = $store->getData();
        self::assertEquals(1, $resultset->getFieldCount());

        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand')->limit(1)->columns(['reference', 'brand_id']);

        $store = $this->getStore($select);
        $resultset = $store->getData();
        self::assertEquals(2, $resultset->getFieldCount());

        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand')->limit(1);

        $store = $this->getStore($select);
        $resultset = $store->getData();
        self::assertEquals(14, $resultset->getFieldCount());
    }

    public function testGetTotal()
    {
        // With limit in the rquery
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand')->limit(10)->columns(['reference']);

        $store = $this->getStore($select);
        //$options = new \Soluble\FlexStore\Options();
        //$options->setLimit(10);
        $resultset = $store->getData();
        $total = $resultset->getTotalRows();

        self::assertEquals(10, $total);
        self::assertCount(10, $resultset);
        self::assertEquals(10, $resultset->count());

        // With no limit
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand')->columns(['reference']);

        $store = $this->getStore($select);
        $options = new \Soluble\FlexStore\Options();
        $options->setLimit(10);
        $resultset = $store->getSource()->getData($options);
        $total = $resultset->getTotalRows();

        self::assertEquals(93, $total);
        self::assertCount(10, $resultset);
    }

    /**
     * @covers \Soluble\FlexStore\ResultSet\ResultSet::getPaginator
     */
    public function testGetPaginatorThrowsInvalidUsageException()
    {
        $this->expectException(\Soluble\FlexStore\Exception\InvalidUsageException::class);
        $paginator = $this->resultset->getPaginator();
        self::assertInstanceOf(\Zend\Paginator\Paginator::class, $paginator);
    }

    /**
     * @covers \Soluble\FlexStore\ResultSet\ResultSet::getPaginator
     */
    public function testGetPaginator()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand');

        $store = $this->getStore($select);
        $source = $store->getSource();
        $source->getOptions()->setLimit(10, 0);
        $resultset = $source->getData();
        $paginator = $resultset->getPaginator();

        self::assertInstanceOf(\Soluble\FlexStore\Helper\Paginator::class, $paginator);

        $pages = $paginator->getPages();

        self::assertEquals(10, $pages->itemCountPerPage);
        self::assertEquals(1, $pages->first);
        self::assertEquals(1, $pages->current);
    }
}
