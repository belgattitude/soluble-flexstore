<?php

namespace SolubleTest\FlexStore\Formatter;

use Soluble\FlexStore\Formatter\UnitFormatter;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-10-08 at 17:24:20.
 */
class UnitFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UnitFormatter
     */
    protected $uf;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->uf = new UnitFormatter;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testConstruct()
    {
        $params = [
            'locale' => 'zh_CN',
            'pattern' => '#,##0.###',
            'decimals' => 3
        ];
        $f = new UnitFormatter($params);
        $this->assertEquals('zh_CN', $f->getLocale());
        $this->assertEquals('#,##0.###', $f->getPattern());
        $this->assertEquals(3, $f->getDecimals());
    }

    public function testGetSet()
    {
        $f = $this->uf;
        $this->assertInternalType('string', $f->getLocale());
        $this->assertEquals($f->getLocale(), substr(\Locale::getDefault(), 0, 5));
        $this->assertNull($f->getPattern());
        $this->assertNull($f->getUnit());
        $this->assertEquals(2, $f->getDecimals());

        $f->setDecimals(3);
        $f->setPattern('#,##0.###');
        $f->setLocale('zh_CN');
        $f->setUnit('Kg');

        $this->assertEquals('Kg', $f->getUnit());
        $this->assertEquals('zh_CN', $f->getLocale());
        $this->assertEquals('#,##0.###', $f->getPattern());
        $this->assertEquals(3, $f->getDecimals());
    }

    public function testFormat()
    {
        $params = [
            'locale' => 'fr_FR',
            'pattern' => '#,##0.###',
            'decimals' => 3,
            'unit' => 'Kg'
        ];
        $f = new UnitFormatter($params);
        $this->assertEquals('1 123,457 Kg', $f->format(1123.4567));
        $this->assertEquals('-1 123,457 Kg', $f->format(-1123.4567));

        $f->setLocale('en_US');
        $this->assertEquals('1,123.457 Kg', $f->format(1123.4567));
        $this->assertEquals('-1,123.457 Kg', $f->format(-1123.4567));

        $params = [
            'locale' => 'fr_BE',
            'unit' => 'm³'
        ];

        $f = new UnitFormatter($params);

        $this->assertEquals('1.128,46 m³', $f->format(1128.4567));
        $params = [
            'locale' => 'en_GB',
            'unit' => 'm²'
        ];
        $f = new UnitFormatter($params);
        $this->assertEquals('1,123.46 m²', $f->format(1123.4567));

        // Null values
        $this->assertEquals('0.00 m²', $f->format(null));
    }

    public function testConstructThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\FlexStore\Exception\InvalidArgumentException');
        $params = [
            'cool' => 0
        ];
        $f = new UnitFormatter($params);
    }

    public function testFormatThrowsRuntimeException2()
    {
        $this->setExpectedException('Soluble\FlexStore\Exception\RuntimeException');
        $params = [
            'locale' => 'fr_FR',
            'decimals' => 3,
            'unit' => 'ltr'
        ];
        $f = new UnitFormatter($params);
        $f->format(['cool']);
    }

    public function testFormatThrowsRuntimeException3()
    {
        $this->setExpectedException('Soluble\FlexStore\Exception\RuntimeException');
        $params = [
            'locale' => 'fr_FR',
            'decimals' => 3,
            'unit' => 'ltr'
        ];

        $f = new UnitFormatter($params);
        $f->format('not a number');
    }
}
