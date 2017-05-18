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

namespace Soluble\FlexStore;

class Formatter
{
    const FORMATTER_CURRENCY = 'currency';
    const FORMATTER_NUMBER = 'number';
    const FORMATTER_UNIT = 'unit';

    /**
     * @var array
     */
    protected static $formattersMap = [
        self::FORMATTER_CURRENCY => 'Formatter\CurrencyFormatter',
        self::FORMATTER_NUMBER => 'Formatter\NumberFormatter',
        self::FORMATTER_UNIT => 'Formatter\UnitFormatter',
    ];

    /**
     * @param string $formatter_name
     *
     * @throws Exception\InvalidArgumentException
     *
     * @return \Soluble\FlexStore\Formatter\FormatterInterface
     */
    public static function create($formatter_name, array $params = [])
    {
        if (!self::isSupported($formatter_name)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Formatter '$formatter_name' is not supported.");
        }
        $class = __NAMESPACE__ . '\\' . self::$formattersMap[strtolower($formatter_name)];

        return new $class($params);
    }

    /**
     * Whether a formatter is supported.
     *
     * @param string $formatter_name
     *
     * @return bool
     */
    public static function isSupported($formatter_name)
    {
        return array_key_exists(strtolower($formatter_name), self::$formattersMap);
    }

    /**
     * Return supported formatters.
     *
     * @return array
     */
    public static function getSupported()
    {
        return array_keys(self::$formattersMap);
    }
}
