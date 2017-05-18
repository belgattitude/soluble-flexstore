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

namespace Soluble\FlexStore\Column;

use Soluble\FlexStore\Formatter\FormatterInterface;

interface ColumnSettableInterface
{
    /**
     * Set column datatype.
     *
     * @param string|\Soluble\FlexStore\Column\Type\AbstractType $type
     *
     * @throws Exception\InvalidArgumentException when the type is not supported
     */
    public function setType($type);

    /**
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter);

    /**
     * @param bool $excluded
     */
    public function setExcluded($excluded = true);

    /**
     * @param bool $virtual
     */
    public function setVirtual($virtual = true);

    /**
     * @param bool $editable
     */
    public function setEditable($editable = true);

    /**
     * @param bool $hidden
     */
    public function setHidden($hidden = true);

    /**
     * @param bool $sortable
     */
    public function setSortable($sortable = true);

    /**
     * @param bool $groupable
     */
    public function setGroupable($groupable = true);

    /**
     * @param bool $filterable
     */
    public function setFilterable($filterable = true);

    /**
     * Set recommended width for the column.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param float|int|string $width
     */
    public function setWidth($width);

    /**
     * Set table header for this column.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param string|null $header
     */
    public function setHeader($header);
}
