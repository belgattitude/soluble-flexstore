<?php

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
