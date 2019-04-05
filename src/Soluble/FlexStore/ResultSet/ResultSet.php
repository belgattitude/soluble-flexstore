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

namespace Soluble\FlexStore\ResultSet;

use Soluble\FlexStore\Exception\InvalidUsageException;
use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Helper\Paginator;
use Soluble\FlexStore\Options\HydrationOptions;
use Soluble\FlexStore\Column\ColumnModel;
use ArrayObject;

class ResultSet extends AbstractResultSet
{
    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @var int|null
     */
    protected $totalRows;

    /**
     * @var AbstractSource
     */
    protected $source;

    /**
     * @var HydrationOptions|null
     */
    protected $hydrationOptions;

    /**
     * @var bool
     */
    protected $hydrate_options_initialized = false;

    /**
     * @var ArrayObject
     */
    protected $hydration_formatters;

    /**
     * @var ArrayObject
     */
    protected $hydration_renderers;

    /**
     * @var ArrayObject|null
     */
    protected $hydrated_columns;

    /**
     * Return source column model.
     *
     * @throws Exception\RuntimeException
     */
    public function getColumnModel(): ColumnModel
    {
        if ($this->source === null) {
            throw new Exception\RuntimeException(__METHOD__ . ' Prior to get column model, a source must be set.');
        }
        $this->hydrate_options_initialized = false;

        return $this->source->getColumnModel();
    }

    /**
     * @param AbstractSource $source
     */
    public function setSource(AbstractSource $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getSource(): AbstractSource
    {
        return $this->source;
    }

    public function setHydrationOptions(HydrationOptions $hydrationOptions): self
    {
        $this->hydrationOptions = $hydrationOptions;

        return $this;
    }

    public function getHydrationOptions(): ?HydrationOptions
    {
        return $this->hydrationOptions;
    }

    public function getPaginator(): Paginator
    {
        if ($this->paginator === null) {
            $limit = $this->getSource()->getOptions()->getLimit();
            $totalRows = $this->getTotalRows() ?? 0;
            if (!is_int($limit)) {
                throw new InvalidUsageException(sprintf(
                    'Paginator requires a limit to be set.'
                ));
            }

            $this->paginator = new Paginator(
                $this->getTotalRows(),
                $limit,
                $this->getSource()->getOptions()->getOffset()
            );
        }

        return $this->paginator;
    }

    /**
     * Set the total rows.
     */
    public function setTotalRows(int $totalRows): self
    {
        $this->totalRows = $totalRows;

        return $this;
    }

    public function getTotalRows(): ?int
    {
        return $this->totalRows;
    }

    protected function initColumnModelHydration(ArrayObject $row): void
    {
        $this->hydration_formatters = new ArrayObject();
        $this->hydration_renderers = new ArrayObject();
        $this->hydrated_columns = null;

        if ($this->source->hasColumnModel()) {
            $cm = $this->getColumnModel();

            // 1. Initialize columns hydrators
            if ($this->getHydrationOptions()->isFormattersEnabled()) {
                $formatters = $cm->getUniqueFormatters();
                if ($formatters->count() > 0) {
                    $this->hydration_formatters = $formatters;
                }
            }

            // 2. Initialize hydrated columns

            if ($this->getHydrationOptions()->isColumnExclusionEnabled()) {
                $columns = $cm->getColumns();

                // Performance:
                // Only if column model definition differs from originating
                // source row definition.
                $hydrated_columns = array_keys((array) $columns);
                $row_columns = array_keys((array) $row);
                if ($hydrated_columns != $row_columns) {
                    $this->hydrated_columns = new ArrayObject($hydrated_columns);
                }
            }

            // 3. Initialize row renderers
            if ($this->getHydrationOptions()->isRenderersEnabled()) {
                $this->hydration_renderers = $cm->getRowRenderers();
            }
        }
        $this->hydrate_options_initialized = true;
    }

    /**
     * Return the current row as an array|ArrayObject.
     * If setLimitColumns() have been set, will only return
     * the limited columns.
     *
     * @throws Exception\UnknownColumnException
     *
     * @return array|ArrayObject|null
     */
    public function current()
    {
        $row = $this->zfResultSet->current();
        if ($row === null) {
            return null;
        }

        if (!$this->hydrate_options_initialized) {
            $this->initColumnModelHydration($row);
        }

        // 1. Row renderers
        foreach ($this->hydration_renderers as $renderer) {
            $renderer->apply($row);
        }

        // 2. Formatters
        foreach ($this->hydration_formatters as $formatters) {
            foreach ($formatters['columns'] as $column) {
                $row[$column] = $formatters['formatter']->format($row[$column], $row);
            }
        }

        // 3. Process column hydration
        if ($this->hydrated_columns !== null) {
            $d = new ArrayObject();
            foreach ($this->hydrated_columns as $column) {
                $d->offsetSet($column, isset($row[$column]) ? $row[$column] : null);
            }
            $row->exchangeArray($d);
        }

        if ($this->returnType === self::TYPE_ARRAY) {
            return (array) $row;
        }

        return $row;
    }

    /**
     * Cast result set to array of arrays.
     *
     * @throws Exception\RuntimeException if any row cannot be casted to an array
     */
    public function toArray(): array
    {
        $return = [];
        foreach ($this as $row) {
            if (is_array($row)) {
                $return[] = $row;
            } elseif (method_exists($row, 'toArray')) {
                $return[] = $row->toArray();
            } elseif (method_exists($row, 'getArrayCopy')) {
                $return[] = $row->getArrayCopy();
            } else {
                throw new Exception\RuntimeException(
                    __METHOD__ . ': Rows as part of this DataSource, with type ' . gettype($row) . ' cannot be cast to an array'
                );
            }
        }

        return $return;
    }

    /**
     * Iterator: is pointer valid?
     */
    public function valid(): bool
    {
        $valid = $this->zfResultSet->valid();
        if (!$valid) {
            $this->hydrate_options_initialized = false;
        }

        return $valid;
    }

    /**
     * Iterator: rewind.
     */
    public function rewind(): void
    {
        $this->hydrate_options_initialized = false;
        $this->zfResultSet->rewind();
    }
}
