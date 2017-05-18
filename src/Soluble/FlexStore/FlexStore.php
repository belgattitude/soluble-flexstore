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

namespace Soluble\FlexStore;

use Soluble\FlexStore\ResultSet\ResultSet;
use Soluble\FlexStore\Store\StoreInterface;

class FlexStore implements StoreInterface
{
    /**
     * @var Source\SourceInterface
     */
    protected $source;

    /**
     * @param Source\SourceInterface $source
     */
    public function __construct(Source\SourceInterface $source)
    {
        $this->source = $source;
    }

    /**
     * Return store search options.
     *
     * @return Options
     */
    public function getOptions()
    {
        return $this->source->getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Return the underlying store data as a resultset.
     *
     * @throws Exception\EmptyQueryException when query is empty
     * @throws Exception\ErrorException      whenever an error occured
     *
     * @param Options $options
     *
     * @return ResultSet
     */
    public function getData(Options $options = null)
    {
        return $this->source->getData($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnModel()
    {
        return $this->source->getColumnModel();
    }
}
