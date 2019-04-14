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

namespace SolubleTest\FlexStore\Formatter;

use PHPUnit\Framework\TestCase;
use Soluble\FlexStore\Formatter\NumberFormatter;

class NumberFormatterTest extends TestCase
{
    /**
     * @var NumberFormatter
     */
    protected $nf;

    protected function setUp(): void
    {
        parent::__construct();
        $this->nf = new NumberFormatter();
    }

    public function testConstruct(): void
    {
        $params = [
            'locale' => 'zh_CN',
            'pattern' => '#,##0.###',
            'decimals' => 3
        ];
        $f = new NumberFormatter($params);
        self::assertEquals('zh_CN', $f->getLocale());
        self::assertEquals('#,##0.###', $f->getPattern());
        self::assertEquals(3, $f->getDecimals());
    }

    public function testGetSet(): void
    {
        $f = $this->nf;
        self::assertInternalType('string', $f->getLocale());
        self::assertEquals($f->getLocale(), substr(\Locale::getDefault(), 0, 5));
        self::assertNull($f->getPattern());
        self::assertEquals(2, $f->getDecimals());

        $f->setDecimals(3);
        $f->setPattern('#,##0.###');
        $f->setLocale('zh_CN');

        self::assertEquals('zh_CN', $f->getLocale());
        self::assertEquals('#,##0.###', $f->getPattern());
        self::assertEquals(3, $f->getDecimals());
    }

    public function testFormat()
    {
        $params = [
            'locale' => 'fr_FR',
            'pattern' => '#,##0.###',
            'decimals' => 3
        ];

        $f = new NumberFormatter($params);

        self::assertEquals('1 123,457', $f->format(1123.4567));
        self::assertEquals('-1 123,457', $f->format(-1123.4567));

        $f->setLocale('en_US');
        self::assertEquals('1,123.457', $f->format(1123.4567));
        self::assertEquals('-1,123.457', $f->format(-1123.4567));

        $params = [
            'locale' => 'fr_BE'
        ];
        $f = new NumberFormatter($params);
        self::assertEquals('1 123,46', $f->format(1123.4567));

        $params = [
            'locale' => 'en_GB'
        ];
        $f = new NumberFormatter($params);
        self::assertEquals('1,123.46', $f->format(1123.4567));
    }

    public function testConstructThrowsInvalidArgumentException()
    {
        $this->expectException('Soluble\FlexStore\Exception\InvalidArgumentException');
        $params = [
            'cool' => 0
        ];
        $f = new NumberFormatter($params);
    }

    public function testFormatThrowsRuntimeException2()
    {
        $this->expectException('Soluble\FlexStore\Exception\RuntimeException');
        $params = [
            'locale' => 'fr_FR',
            'decimals' => 3
        ];
        $f = new NumberFormatter($params);
        $f->format(['cool']);
    }

    public function testFormatThrowsRuntimeException3()
    {
        $this->expectException('Soluble\FlexStore\Exception\RuntimeException');
        $params = [
            'locale' => 'fr_FR',
            'decimals' => 3
        ];
        $f = new NumberFormatter($params);
        $a = $f->format('not a number');
    }
}
