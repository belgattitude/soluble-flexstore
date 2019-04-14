<?php

declare(strict_types=1);

/*
 * soluble-flexstore library
 *
 * @author    Vanvelthem Sébastien
 * @link      https://github.com/belgattitude/soluble-flexstore
 * @copyright Copyright (c) 2016-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-flexstore/blob/master/LICENSE.md
 *
 */

namespace Soluble\FlexStore\Formatter;

use Soluble\FlexStore\Exception;
use ArrayObject;
use NumberFormatter as IntlNumberFormatter;

/**
 * columns
 *  - price:
 *    - formatter:
 *          - money
 *              - currency_code
 *              - locale.
 */
class CurrencyFormatter extends NumberFormatter
{
    /**
     * @var string|null
     */
    protected $currency_column;

    /**
     * @var array
     */
    protected $default_params = [
        'decimals' => 2,
        'locale' => null,
        'pattern' => null,
        'currency_code' => null,
        'disableUseOfNonBreakingSpaces' => false
    ];

    /**
     * @throws Exception\ExtensionNotLoadedException if ext/intl is not present
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
    }

    protected function loadFormatterId(string $formatterId): void
    {
        $locale = $this->params['locale'];
        $formatter = new IntlNumberFormatter(
            $locale,
            IntlNumberFormatter::CURRENCY
        );
        $formatter->setAttribute(IntlNumberFormatter::FRACTION_DIGITS, $this->params['decimals']);
        if ($this->params['pattern'] !== null) {
            $formatter->setPattern($this->params['pattern']);
        }
        $this->initWhitespaceSeparator($formatter);
        $this->formatters[$formatterId] = $formatter;
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
    public function format($number, ArrayObject $row = null): string
    {
        $locale = $this->params['locale'];

        //$formatterId = md5($locale);
        $formatterId = $locale . (string) $this->params['pattern'];

        if (!array_key_exists($formatterId, $this->formatters)) {
            $this->loadFormatterId($formatterId);
        }

        if ($this->currency_column !== null) {
            if (!isset($row[$this->currency_column])) {
                throw new Exception\RuntimeException(__METHOD__ . " Cannot determine currency code based on column '{$this->currency_column}'.");
            }
            $value = $this->formatters[$formatterId]->formatCurrency(
                (float) $number,
                $row[$this->currency_column]
            );
        } else {
            if ($this->params['currency_code'] == '') {
                throw new Exception\RuntimeException(__METHOD__ . ' Currency code must be set prior to use the currency formatter');
            }

            $value = $this->formatters[$formatterId]->formatCurrency(
                (float) $number,
                $this->params['currency_code']
            );
        }

        if (intl_is_failure($this->formatters[$formatterId]->getErrorCode())) {
            $this->throwNumberFormatterException($this->formatters[$formatterId], $number);
        }

        return $value;
    }

    /**
     * Parse a.
     *
     * @param string $value
     *
     * @return array|null
     */
    public function parse($value)
    {
        $locale = $this->params['locale'];
        //$formatterId = md5($locale);
        $formatterId = $locale;
        if (!array_key_exists($formatterId, $this->formatters)) {
            $this->loadFormatterId($formatterId);
        }
        // Currency will be passed as reference
        // setting it to null prevent eventual warning
        $currency = null;
        $result = $this->formatters[$formatterId]->parseCurrency($value, $currency);

        if ($result === false) {
            return null;
        }

        return ['value' => $result, 'currency' => $currency];
    }

    /**
     * The 3-letter ISO 4217 currency code indicating the currency to use.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param string|RowColumn $currencyCode
     */
    public function setCurrencyCode($currencyCode): self
    {
        if ($currencyCode instanceof RowColumn) {
            $this->currency_column = $currencyCode->getColumnName();
        } elseif (!is_string($currencyCode) || trim($currencyCode) === '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' Currency code must be an non empty string (or a RowColumn object)');
        }
        $this->params['currency_code'] = $currencyCode;

        return $this;
    }

    protected function initWhitespaceSeparator(IntlNumberFormatter $formatter): void
    {
        parent::initWhitespaceSeparator($formatter);
        if ($this->params['disableUseOfNonBreakingSpaces'] === true
            && in_array(bin2hex($formatter->getSymbol(IntlNumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL)), [
                self::NARROW_NO_BREAK_SPACE_HEX,
                self::NO_BREAK_SPACE_HEX
            ], true)) {

            $formatter->setSymbol(IntlNumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, ' ');
            // This does not work on php 7.3 (bug) !
            //$formatter->setSymbol(IntlNumberFormatter::MONETARY_SEPARATOR_SYMBOL, ' ');

        }
    }

    /**
     * Get the 3-letter ISO 4217 currency code indicating the currency to use.
     *
     * @return string|RowColumn
     */
    public function getCurrencyCode()
    {
        return $this->params['currency_code'];
    }
}
