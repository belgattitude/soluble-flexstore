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

namespace SolubleTest\FlexStore;

use PHPUnit\Framework\TestCase;
use Soluble\FlexStore\Options;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-10-01 at 11:53:48.
 */
class OptionsTest extends TestCase
{
    /**
     * @var Options
     */
    protected $options;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->options = new Options();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testSetGetLimitAndOffset()
    {
        $this->assertNull($this->options->getLimit());
        $this->assertFalse($this->options->hasLimit());
        $this->options->setLimit(10);
        $this->assertEquals(10, $this->options->getLimit());
        $this->assertTrue($this->options->hasLimit());

        $this->assertNull($this->options->getOffset());
        $this->assertFalse($this->options->hasOffset());

        $this->options->setLimit(40, 50);
        $this->assertEquals(50, $this->options->getOffset());
        $this->options->setLimit(10);
        $this->assertEquals(50, $this->options->getOffset());
        $this->options->unsetOffset();
        $this->assertNull($this->options->getOffset());
        $this->assertFalse($this->options->hasOffset());

        $this->options->unsetLimit();
        $this->assertNull($this->options->getLimit());
        $this->assertFalse($this->options->hasLimit());
    }
}
