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

namespace Soluble\FlexStore\Formatter;

use ArrayObject;

interface FormatterInterface
{
    /**
     * @param array $params
     */
    public function __construct(array $params = []);

    /**
     * Format.
     *
     * @param mixed       $value
     * @param ArrayObject $row
     */
    public function format($value, ArrayObject $row);
}
