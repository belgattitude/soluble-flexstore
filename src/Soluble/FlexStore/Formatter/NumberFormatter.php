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
use Soluble\FlexStore\I18n\LocalizableInterface;
use ArrayObject;
use Locale;
use NumberFormatter as IntlNumberFormatter;

class NumberFormatter implements FormatterInterface, LocalizableInterface, FormatterNumberInterface
{
    public const NO_BREAK_SPACE_HEX = 'c2a0';
    public const NARROW_NO_BREAK_SPACE_HEX = 'e280af';

    /**
     * Formatter instances.
     *
     * @var array
     */
    protected $formatters = [];

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var array
     */
    protected $default_params = [
        'decimals' => 2,
        'locale' => null,
        'pattern' => null,
        'disableUseOfNonBreakingSpaces' => false
    ];

    /**
     * @param array $params
     *
     * @throws Exception\ExtensionNotLoadedException if ext/intl is not present
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(array $params = [])
    {
        if (!extension_loaded('intl')) {
            throw new Exception\ExtensionNotLoadedException(sprintf(
                '%s component requires the intl PHP extension',
                __NAMESPACE__
            ));
        }

        // As default locale may include unsupported
        // variants (like 'en_US_POSIX' for example),
        // only the 5 chars will be taken into consideration

        $default_locale = Locale::getDefault();
        $this->default_params['locale'] = substr($default_locale, 0, 5);
        $this->setParams($params);
    }

    /**
     * @throws Exception\InvalidArgumentException
     *
     * @param array $params
     */
    protected function setParams($params)
    {
        $this->params = $this->default_params;
        foreach ($params as $name => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($name))));
            if (!method_exists($this, $method)) {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Parameter '$name' does not exists.");
            }
            $this->$method($value);
        }
    }

    protected function initWhitespaceSeparator(IntlNumberFormatter $formatter): void
    {
        if ($this->params['disableUseOfNonBreakingSpaces'] === true
        && in_array(bin2hex($formatter->getSymbol(IntlNumberFormatter::GROUPING_SEPARATOR_SYMBOL)), [
                self::NARROW_NO_BREAK_SPACE_HEX,
                self::NO_BREAK_SPACE_HEX
            ], true)) {

            $formatter->setSymbol(IntlNumberFormatter::GROUPING_SEPARATOR_SYMBOL, ' ');
        }
    }

    protected function loadFormatterId(string $formatterId): void
    {
        $locale = $this->params['locale'];
        $formatter = new IntlNumberFormatter(
            $locale,
            IntlNumberFormatter::DECIMAL
        );
        $formatter->setAttribute(IntlNumberFormatter::FRACTION_DIGITS, $this->params['decimals']);
        if ($this->params['pattern'] !== null) {
            $formatter->setPattern($this->params['pattern']);
        }

        $this->initWhitespaceSeparator($formatter);

        $this->formatters[$formatterId] = $formatter;
    }

    /**
     * Format a number.
     *
     * @param float $number
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

        if (!is_numeric($number)) {
            $this->throwNumberFormatterException($this->formatters[$formatterId], $number);
        }

        return $this->formatters[$formatterId]->format($number);
    }

    /**
     * Throws an Exception when number cannot be formatted.
     *
     * @param IntlNumberFormatter $intlFormatter
     * @param int|string|float    $number
     *
     * @throws Exception\RuntimeException
     */
    protected function throwNumberFormatterException(IntlNumberFormatter $intlFormatter, $number): void
    {
        $error_code = $intlFormatter->getErrorCode();
        if (is_scalar($number)) {
            $val = (string) $number;
        } else {
            $val = 'type: ' . gettype($number);
        }
        throw new Exception\RuntimeException(__METHOD__ . " Cannot format value '$val', Intl/NumberFormatter error code: $error_code.");
    }

    /**
     * Set locale to use instead of the default.
     *
     * @param string $locale
     */
    public function setLocale(?string $locale): self
    {
        $this->params['locale'] = $locale;

        return $this;
    }

    /**
     * Get the locale to use.
     *
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->params['locale'];
    }

    /**
     * Set decimals.
     *
     * @param int $decimals
     */
    public function setDecimals($decimals): self
    {
        $this->params['decimals'] = $decimals;

        return $this;
    }

    /**
     * @return int
     */
    public function getDecimals(): int
    {
        return $this->params['decimals'];
    }

    /**
     * Set the number pattern, (#,##0.###, ....).
     *
     * @see http://php.net/manual/en/numberformatter.setpattern.php
     *
     * @param string $pattern
     */
    public function setPattern($pattern): self
    {
        $this->params['pattern'] = $pattern;

        return $this;
    }

    /**
     * Get the number pattern.
     *
     * @return string|null
     */
    public function getPattern(): ?string
    {
        return $this->params['pattern'];
    }

    public function setDisableUseOfNonBreakingSpaces(bool $disable=true): self {
        $this->params['disableUseOfNonBreakingSpaces'] = $disable;
        return $this;
    }
}
