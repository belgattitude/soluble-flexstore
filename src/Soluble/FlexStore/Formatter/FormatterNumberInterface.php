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

interface FormatterNumberInterface
{
    /**
     * Set decimals.
     *
     * @param int $decimals
     */
    public function setDecimals($decimals);

    /**
     * @return int
     */
    public function getDecimals();
}
