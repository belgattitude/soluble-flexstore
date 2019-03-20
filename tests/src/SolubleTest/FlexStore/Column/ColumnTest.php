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

namespace SolubleTest\FlexStore\Column;

use PHPUnit\Framework\TestCase;
use Soluble\FlexStore\Column\Column;
use Soluble\FlexStore\Column\ColumnType;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-09-30 at 16:51:25.
 */
class ColumnTest extends TestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testGetProperties()
    {
        $formatter = new \Soluble\FlexStore\Formatter\CurrencyFormatter();
        $properties = [
            'type' => 'string', // will be defaulted to string
            'formatter' => $formatter,
            'excluded' => true,
            'hidden' => true,
            'width' => '100%',
            'header' => 'cool', // will be defaulted to name
            'filterable' => false,
            'groupable' => false,
            'sortable' => false,
            'editable' => true,
            'virtual' => true,
            'align' => 'right',
            'class' => 'css_class'
        ];

        $column = new Column('cool', $properties);
        $this->assertEquals($properties, $column->getProperties());
    }

    public function testWithProperties()
    {
        $properties = [
            'type' => ColumnType::TYPE_DATE,
            'header' => 'header',
            'width' => '100%',
            'filterable' => false,
            'groupable' => false,
            'sortable' => false,
            'hidden' => true,
            'excluded' => true,
            'editable' => true
        ];

        $column = new Column('cool', $properties);
        $this->assertFalse($column->isSortable());
        $this->assertFalse($column->isGroupable());
        $this->assertFalse($column->isFilterable());
        $this->assertTrue($column->isHidden());
        $this->assertTrue($column->isExcluded());
        $this->assertTrue($column->isEditable());
        $this->assertNull($column->getClass());
        $this->assertNull($column->getAlign());
        $this->assertEquals('header', $column->getHeader());
        $this->assertEquals(ColumnType::TYPE_DATE, $column->getType());
        $this->assertEquals('100%', $column->getWidth());

        $properties = [
            'header' => 'changed',
        ];
        $column->setProperties($properties);
        $this->assertEquals('changed', $column->getHeader());
    }

    public function testSetPropertiesThrowsInvalidArgumentException()
    {
        $this->expectException('Soluble\FlexStore\Column\Exception\InvalidArgumentException');

        $properties = [
            'header' => 'header',
            'not_exists' => '100%',
        ];

        $column = new Column('cool', $properties);
    }

    public function setTypeThrowsInvalidArgumentException()
    {
        $this->expectException('Soluble\FlexStore\Column\Exception\InvalidArgumentException');
        $type = new \stdClass();
        $column = new Column('cool');
        $column->setType($type);
    }

    public function testSetPropertiesThrowsInvalidArgumentException2()
    {
        $this->expectException('Soluble\FlexStore\Column\Exception\InvalidArgumentException');

        $properties = [
            'header' => 'header',
            'not_exists' => '100%',
        ];

        $column = new Column('cool');
        $column->setProperties($properties);
    }

    public function testGetSet()
    {
        $column = new Column('cool');
        $this->assertEquals('cool', $column->getName());

        $this->assertEquals('cool', $column->getHeader());
        $column->setHeader('Hello');
        $this->assertEquals('Hello', $column->getHeader());

        // Width
        $this->assertNull($column->getWidth());
        $column->setWidth(10);
        $this->assertEquals(10, $column->getWidth());

        // Groupable
        $this->assertTrue($column->isGroupable());
        $column->setGroupable($groupable = true);
        $this->assertTrue($column->isGroupable());
        $column->setGroupable($groupable = false);
        $this->assertFalse($column->isGroupable());

        // Hidden
        $this->assertFalse($column->isHidden());
        $column->setHidden();
        $this->assertTrue($column->isHidden());
        $column->setHidden(false);
        $this->assertFalse($column->isHidden());

        // Filterable
        $this->assertTrue($column->isFilterable());
        $column->setFilterable();
        $this->assertTrue($column->isFilterable());
        $column->setFilterable(false);
        $this->assertFalse($column->isFilterable());

        // Excluded
        $this->assertFalse($column->isExcluded());
        $column->setExcluded();
        $this->assertTrue($column->isExcluded());
        $column->setExcluded(false);
        $this->assertFalse($column->isExcluded());

        // Sortable
        $this->assertTrue($column->isSortable());
        $column->setSortable();
        $this->assertTrue($column->isSortable());
        $column->setSortable(false);
        $this->assertFalse($column->isSortable());

        // Editable
        $this->assertFalse($column->isEditable());
        $column->setEditable();
        $this->assertTrue($column->isEditable());
        $column->setEditable(false);
        $this->assertFalse($column->isEditable());
    }

    public function testConstructThrowsInvalidArgumentException()
    {
        $this->expectException('Soluble\FlexStore\Column\Exception\InvalidArgumentException');
        $column = new Column(['cool']);
    }

    public function testConstructThrowsInvalidArgumentException2()
    {
        $this->expectException('Soluble\FlexStore\Column\Exception\InvalidArgumentException');
        $column = new Column(' ');
    }

    public function testSetWidthThrowsInvalidArgumentException()
    {
        $this->expectException('Soluble\FlexStore\Column\Exception\InvalidArgumentException');
        $column = new Column('hello');
        $column->setWidth(['cool']);
    }

    /**
     * @covers \Soluble\FlexStore\Column\Column::__toString
     */
    public function test__toString()
    {
        $column = new Column('hello');
        $this->assertEquals('hello', (string) $column);
    }
}
