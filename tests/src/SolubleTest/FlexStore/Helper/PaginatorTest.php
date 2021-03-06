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

namespace SolubleTest\FlexStore\Helper;

use PHPUnit\Framework\TestCase;
use Soluble\FlexStore\Helper\Paginator;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-10-17 at 12:54:41.
 */
class PaginatorTest extends TestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
    }

    public function testSomeException()
    {
        try {
            $p = new Paginator(0, -5, 0);
            self::assertFalse(true, 'should throw an InvalidUsagException');
        } catch (\Soluble\FlexStore\Exception\InvalidUsageException $ex) {
            self::assertTrue(true);
        }

        try {
            $p = new Paginator(20, 10, -2);
            self::assertFalse(true, 'should throw an InvalidUsagException');
        } catch (\Soluble\FlexStore\Exception\InvalidUsageException $ex) {
            self::assertTrue(true);
        }

        try {
            $p = new Paginator(-15, 10, 0);
            self::assertFalse(true, 'should throw an InvalidUsagException');
        } catch (\Soluble\FlexStore\Exception\InvalidUsageException $ex) {
            self::assertTrue(true);
        }
    }

    public function testConstruct()
    {
        $p = new Paginator(150, 10, 20);
        self::assertEquals(10, $p->getItemCountPerPage());
        self::assertEquals(3, $p->getCurrentPageNumber());
        self::assertEquals(150, $p->getTotalItemCount());
    }
}
