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

class RowColumn
{
    /**
     * @var string
     */
    protected $column_name;

    /**
     * @param string $column_name
     */
    public function __construct($column_name)
    {
        $this->column_name = $column_name;
    }

    /**
     * @return string
     */
    public function getColumnName()
    {
        return $this->column_name;
    }
}
