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

interface SourceInterface
{
    /**
     * @param Options $options
     *
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    public function getData(Options $options = null);

    /**
     * @return \Soluble\FlexStore\Column\ColumnModel
     */
    public function getColumnModel();

    /**
     * @return \Soluble\Metadata\Reader\AbstractMetadataReader
     */
    public function getMetadataReader();

    /**
     * @return \Soluble\FlexStore\Options
     */
    public function getOptions();

    /**
     * Return underlying query (sql) string if it exists.
     *
     * @return string
     */
    public function getQueryString();
}
