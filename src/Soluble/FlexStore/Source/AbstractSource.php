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

namespace Soluble\FlexStore\Source;

use Soluble\FlexStore\Options;
use Soluble\Metadata\Reader\AbstractMetadataReader;
use Soluble\FlexStore\Column\ColumnModel;

abstract class AbstractSource implements SourceInterface
{
    /**
     * @var \Soluble\FlexStore\Options
     */
    protected $options;

    /**
     * Column model.
     *
     * @var ColumnModel
     */
    protected $columnModel;

    /**
     * @var string|null
     */
    protected $identifier;

    /**
     * @var AbstractMetadataReader
     */
    protected $metadataReader;

    /**
     * @return \Soluble\FlexStore\Options
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = new Options();
        }

        return $this->options;
    }

    /**
     * @param Options $options
     *
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    abstract public function getData(Options $options = null);

    /**
     * Set the primary key / unique identifier in the store.
     *
     * @param string $identifier column name of the primary key
     *
     * @return AbstractSource
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Return the primary key / unique identifier in the store
     * Null if not applicable.
     *
     * @return string|null
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Whether a column model exists.
     *
     * @return bool
     */
    public function hasColumnModel()
    {
        return $this->columnModel !== null;
    }

    /**
     * Return column model.
     *
     * @return ColumnModel
     */
    public function getColumnModel()
    {
        if ($this->columnModel === null) {
            $this->loadDefaultColumnModel();
        }

        return $this->columnModel;
    }

    /**
     * Set column model associated with the datasource.
     *
     * @param ColumnModel $columnModel
     *
     * @return AbstractSource
     */
    public function setColumnModel(ColumnModel $columnModel)
    {
        $this->columnModel = $columnModel;

        return $this;
    }

    /**
     * Default column model initialization.
     */
    abstract public function loadDefaultColumnModel();

    /**
     * Set underlying metadatareader.
     *
     * @param AbstractMetadataReader $metadataReader
     */
    public function setMetadataReader(AbstractMetadataReader $metadataReader)
    {
        $this->metadataReader = $metadataReader;
    }

    /**
     * Return underlying metadata reader.
     *
     * @return AbstractMetadataReader
     */
    abstract public function getMetadataReader();
}
