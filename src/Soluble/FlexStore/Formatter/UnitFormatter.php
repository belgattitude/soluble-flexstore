<?php

/*
 * soluble-flexstore library
 *
 * @author    Vanvelthem Sébastien
 * @link      https://github.com/belgattitude/soluble-flexstore
 * @copyright Copyright (c) 20016-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-flexstore/blob/master/LICENSE.md
 *
 */

namespace Soluble\FlexStore\Formatter;

use Soluble\FlexStore\Exception;
use ArrayObject;

class UnitFormatter extends NumberFormatter
{
    /**
     * @var string|null
     */
    protected $unit_column;

    /**
     * @var array
     */
    protected $default_params = [
        'decimals' => 2,
        'locale' => null,
        'pattern' => null,
        'unit' => null
    ];

    /**
     * @throws Exception\ExtensionNotLoadedException if ext/intl is not present
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
    }

    /**
     * Currency format a number.
     *
     * @throws Exception\RuntimeException
     *
     * @param float|string|int $number
     *
     * @return string
     */
    public function format($number, ArrayObject $row = null)
    {
        $locale = $this->params['locale'];

        //$formatterId = md5($locale);
        $formatterId = $locale . (string) $this->params['pattern'];

        if (!array_key_exists($formatterId, $this->formatters)) {
            $this->loadFormatterId($formatterId);
        }

        if ($number !== null && !is_numeric($number)) {
            $this->throwNumberFormatterException($this->formatters[$formatterId], $number);
        }

        if ($this->unit_column !== null) {
            if (!isset($row[$this->unit_column])) {
                throw new Exception\RuntimeException(__METHOD__ . " Cannot determine unit code based on column '{$this->unit_column}'.");
            }
            $value = $this->formatters[$formatterId]->format($number) . ' ' . $row[$this->unit_column];
        } elseif ($this->params['unit'] != '') {
            $value = $this->formatters[$formatterId]->format($number) . ' ' . $this->params['unit'];
        } else {
            throw new Exception\RuntimeException(__METHOD__ . ' Unit code must be set prior to use the UnitFormatter');
        }

        if (intl_is_failure($this->formatters[$formatterId]->getErrorCode())) {
            $this->throwNumberFormatterException($this->formatters[$formatterId], $number);
        }

        return $value;
    }

    /**
     * @throws Exception\InvalidArgumentException
     *
     * @param string|RowColumn $unit
     *
     * @return UnitFormatter
     */
    public function setUnit($unit)
    {
        if ($unit instanceof RowColumn) {
            $this->unit_column = $unit->getColumnName();
        } elseif (!is_string($unit) || trim($unit) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' Unit must be an non empty string (or a RowColumn object)');
        }
        $this->params['unit'] = $unit;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->params['unit'];
    }
}
