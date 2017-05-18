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

use Soluble\FlexStore\Renderer\RowRendererInterface;
use Soluble\FlexStore\Column\ColumnModel\Search;
use Soluble\FlexStore\Formatter\FormatterInterface;
use Soluble\Metadata\ColumnsMetadata;
use ArrayObject;

class ColumnModel
{
    const ADD_COLUMN_AFTER = 'after';
    const ADD_COLUMN_BEFORE = 'before';

    /**
     * @var ArrayObject
     */
    protected $columns;

    /**
     * @var Search
     */
    protected $search;

    /**
     * @var ArrayObject
     */
    protected $row_renderers;

    /**
     * @var ColumnsMetadata|null
     */
    protected $metadata;

    public function __construct()
    {
        $this->columns = new ArrayObject();
        $this->row_renderers = new ArrayObject();
    }

    /**
     * Add a row renderer.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param RowRendererInterface $renderer
     */
    public function addRowRenderer(RowRendererInterface $renderer)
    {
        // Test if all required columns are present in column model
        $required_columns = $renderer->getRequiredColumns();

        foreach ($required_columns as $column) {
            if (!$this->exists($column)) {
                $cls = get_class($renderer);
                $msg = "Renderer '$cls' requires column '$column' to be present in column model.";
                throw new Exception\MissingColumnException(__METHOD__ . ': ' . $msg);
            }
        }

        $this->row_renderers->append($renderer);
    }

    /**
     * @return ArrayObject
     */
    public function getRowRenderers()
    {
        return $this->row_renderers;
    }

    /**
     * Return an array object containing all
     * columns that have a formatter (FormatterInterface).
     * [column_name] => [FormatterInterface].
     *
     * @see self::getUniqueFormatters()
     *
     * @return ArrayObject
     */
    public function getFormatters()
    {
        $arr = new ArrayObject();
        foreach ($this->columns as $key => $column) {
            if (($formatter = $column->getFormatter()) !== null) {
                $arr->offsetSet($key, $formatter);
            }
        }

        return $arr;
    }

    /**
     * This method returns unique formatters set in the column model
     * in an ArrayObject.
     *
     *
     * @param bool $include_excluded_columns
     *
     * @see self::getFormatters()
     *
     * @return ArrayObject
     */
    public function getUniqueFormatters($include_excluded_columns = false)
    {
        $unique = new ArrayObject();

        $formatters = $this->getFormatters();
        foreach ($formatters as $column => $formatter) {
            if ($include_excluded_columns || !$this->get($column)->isExcluded()) {
                $hash = spl_object_hash($formatter);
                if (!$unique->offsetExists($hash)) {
                    $tmp = new ArrayObject([
                                                'formatter' => $formatter,
                                                'columns' => new ArrayObject([$column])
                    ]);
                    $unique->offsetSet($hash, $tmp);
                } else {
                    $unique->offsetGet($hash)->offsetGet('columns')->append($column);
                }
            }
        }

        return $unique;
    }

    /**
     * Add a new column to the column model.
     *
     * @throws Exception\InvalidArgumentException when mode is not supported
     * @throws Exception\DuplicateColumnException when column name already exists
     * @throws Exception\ColumnNotFoundException  when after_column does not exists
     *
     * @param Column $column
     * @param string $after_column add the new column after this existing one
     * @param string $mode         change after to before (see self::ADD_COLUMN_AFTER, self::ADD_COLUMN_BEFORE)
     *
     * @return ColumnModel
     */
    public function add(Column $column, $after_column = null, $mode = self::ADD_COLUMN_AFTER)
    {
        $name = $column->getName();
        if ($this->exists($name)) {
            $msg = "Cannot add column '$name', it's already present in column model";
            throw new Exception\DuplicateColumnException(__METHOD__ . ': ' . $msg);
        }

        if ($after_column !== null) {
            // Test existence of column
            if (!$this->exists($after_column)) {
                $msg = "Cannot add column '$name' after '$after_column', column does not exists.";
                throw new Exception\ColumnNotFoundException(__METHOD__ . ': ' . $msg);
            }

            if (!in_array($mode, [self::ADD_COLUMN_BEFORE, self::ADD_COLUMN_AFTER])) {
                $msg = "Cannot add column '$name', invalid mode specified '$mode'";
                throw new Exception\InvalidArgumentException(__METHOD__ . ': ' . $msg);
            }

            $new_columns = new ArrayObject();
            foreach ($this->columns as $key => $col) {
                if ($mode == self::ADD_COLUMN_BEFORE && $key == $after_column) {
                    $new_columns->offsetSet($name, $column);
                }
                $new_columns->offsetSet($key, $col);
                if ($mode == self::ADD_COLUMN_AFTER && $key == $after_column) {
                    $new_columns->offsetSet($name, $column);
                }
            }
            $this->columns->exchangeArray($new_columns);
        } else {
            // Simply append
            $this->columns->offsetSet($name, $column);
        }

        return $this;
    }

    /**
     * Tells whether a column exists.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param string $column
     *
     * @return bool
     */
    public function exists($column)
    {
        if (!is_string($column)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' Column name must be a valid string');
        }
        if ($column == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' Column name cannot be empty');
        }

        return $this->columns->offsetExists($column);
    }

    /**
     * Return column that have been excluded in getData() and getColumns().
     *
     * @return array
     */
    public function getExcluded()
    {
        $arr = [];
        foreach ($this->columns as $name => $column) {
            if ($column->isExcluded()) {
                $arr[] = $name;
            }
        }

        return $arr;
    }

    /**
     * Return column from identifier name.
     *
     * @param string $column column name
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ColumnNotFoundException  when column does not exists in model
     *
     * @return Column
     */
    public function get($column)
    {
        if (!$this->exists($column)) {
            throw new Exception\ColumnNotFoundException(__METHOD__ . " Column '$column' not present in column model.");
        }

        return $this->columns->offsetGet($column);
    }

    /**
     * Sort columns in the order specified, columns that exists
     * in the dataset but not in the sorted_columns will be
     * appended to the end.
     *
     * @param array $sorted_columns
     *
     * @return ColumnModel
     */
    public function sort(array $sorted_columns)
    {
        $diff = array_diff_assoc($sorted_columns, array_unique($sorted_columns));
        if (count($diff) > 0) {
            $cols = implode(',', $diff);
            throw new Exception\DuplicateColumnException(__METHOD__ . " Duplicate column found in paramter sorted_columns : '$cols'");
        }
        $columns = [];

        foreach ($sorted_columns as $idx => $column) {
            if (!$this->exists($column)) {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists.");
            }
            $columns[$column] = $this->get($column);
        }
        // Appending eventual non sorted columns at the end
        $columns = array_merge($columns, (array) $this->columns);
        $this->columns->exchangeArray($columns);

        return $this;
    }

    /**
     * Set column that must be excluded in getData() and getColumns().
     *
     * @param array|string|ArrayObject $excluded_columns column nams to exclude
     * @param bool                     $excluded         whether to set exclude to true (default) or false (opposite: include)
     *
     * @throws Exception\InvalidArgumentException
     *
     * @return ColumnModel
     */
    public function exclude($excluded_columns, $excluded = true)
    {
        if (!is_array($excluded_columns) && !is_string($excluded_columns) && !$excluded_columns instanceof ArrayObject) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' Requires $excluded_columns param to be array|ArrayObject|string');
        }
        // trim column
        $excluded_columns = array_map('trim', $excluded_columns);

        $this->search()->in($excluded_columns)->setExcluded($excluded);

        return $this;
    }

    /**
     * Exclude all other columns that the one specified
     * Column sort is preserved in getData().
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param array|string|ArrayObject $include_only_columns
     * @param bool                     $sort                 automatically apply sortColumns
     * @param bool                     $preserve_excluded    preserve excluded columns
     *
     * @return ColumnModel
     */
    public function includeOnly($include_only_columns, $sort = true, $preserve_excluded = true)
    {
        if (!is_array($include_only_columns)
                && !is_string($include_only_columns) && !$include_only_columns instanceof ArrayObject) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' Requires $include_only_columns param to be array|ArrayObject|string');
        }

        // trim column
        $include_only_columns = array_map('trim', (array) $include_only_columns);

        if ($preserve_excluded) {
            $previous_excluded_cols = $this->getExcluded();
        } else {
            $previous_excluded_cols = [];
        }

        $this->search()->all()->setExcluded(true);
        $this->search()->in($include_only_columns)->setExcluded(false);

        if ($sort) {
            $this->sort($include_only_columns);
        }

        if (count($previous_excluded_cols) > 0) {
            $this->exclude($previous_excluded_cols);
        }

        return $this;
    }

    /**
     * Return columns.
     *
     * @param bool $include_excluded_columns
     *
     * @return ArrayObject
     */
    public function getColumns($include_excluded_columns = false)
    {
        $arr = new ArrayObject();
        foreach ($this->columns as $key => $column) {
            if ($include_excluded_columns || !$column->isExcluded()) {
                $arr->offsetSet($key, $column);
            }
        }

        return $arr;
    }

    /**
     * Set formatter to specific columns.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param FormatterInterface       $formatter
     * @param array|string|ArrayObject $columns
     *
     * @return ColumnModel
     */
    public function setFormatter(FormatterInterface $formatter, $columns)
    {
        if (!is_array($columns)
                && !is_string($columns) && !$columns instanceof ArrayObject) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' Requires $columns param to be array|ArrayObject|string');
        }
        $this->search()->in($columns)->setFormatter($formatter);

        return $this;
    }

    /**
     * @return ColumnModel\Search
     */
    public function search()
    {
        if ($this->search === null) {
            $this->search = new Search($this->columns);
        }

        return $this->search;
    }

    /**
     * @param ColumnsMetadata $metadata
     *
     * @return ColumnModel
     */
    public function setMetatadata(ColumnsMetadata $metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @return ArrayObject|null
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
