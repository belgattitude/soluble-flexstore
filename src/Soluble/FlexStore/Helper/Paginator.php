<?php

/*
 * soluble-flexstore library
 *
 * @author    Vanvelthem Sébastien
 * @link      https://github.com/belgattitude/soluble-flexstore
 * @copyright Copyright (c) 20016-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-flexstore/blob/master/LICENSE.md
 *
 */

namespace Soluble\FlexStore\Helper;

use Soluble\FlexStore\Exception;
use Zend\Paginator\Paginator as ZendPaginator;
use Zend\Paginator\ScrollingStyle\ScrollingStyleInterface;

class Paginator extends ZendPaginator
{
    /**
     * Default set of scrolling.
     *
     * @var array
     */
    protected $scrollingTypes = [
        'all' => 'Zend\Paginator\ScrollingStyle\All',
        'elastic' => 'Zend\Paginator\ScrollingStyle\Elastic',
        'jumping' => 'Zend\Paginator\ScrollingStyle\Jumping',
        'sliding' => 'Zend\Paginator\ScrollingStyle\Sliding',
    ];

    /**
     * @param int $totalRows
     * @param int $limit
     * @param int $offset
     *
     * @throws Exception\InvalidUsageException
     */
    public function __construct($totalRows, $limit, $offset = 0)
    {
        $totalRows = filter_var($totalRows, FILTER_VALIDATE_INT);
        $limit = filter_var($limit, FILTER_VALIDATE_INT);
        $offset = filter_var($offset, FILTER_VALIDATE_INT);

        if (!is_int($limit) || $limit < 0) {
            throw new Exception\InvalidUsageException(__METHOD__ . ' expects limit to be an integer greater than 0');
        }
        if (!is_int($totalRows) || $totalRows < 0) {
            throw new Exception\InvalidUsageException(__METHOD__ . ' expects total rows to be an integer greater than 0');
        }
        if (!is_int($offset) || $offset < 0) {
            throw new Exception\InvalidUsageException(__METHOD__ . ' expects offset to be an integer greater than 0');
        }

        if (class_exists('\Zend\Paginator\Adapter\NullFill')) {
            $adapter = new \Zend\Paginator\Adapter\NullFill($totalRows);
        } else {
            throw new Exception\RuntimeException(__METHOD__ . " Missing Zend\Paginator\Adapter.");
        }

        parent::__construct($adapter);
        $this->setItemCountPerPage($limit);
        $this->setCurrentPageNumber(ceil(($offset + 1) / $limit));
    }

    /**
     * Loads a scrolling style.
     *
     * @param string $scrollingStyle
     *
     * @return ScrollingStyleInterface
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function _loadScrollingStyle($scrollingStyle = null)
    {
        if ($scrollingStyle === null) {
            $scrollingStyle = static::$defaultScrollingStyle;
        }

        switch (strtolower(gettype($scrollingStyle))) {
            case 'object':
                if (!$scrollingStyle instanceof ScrollingStyleInterface) {
                    throw new Exception\InvalidArgumentException(
                        'Scrolling style must implement Zend\Paginator\ScrollingStyle\ScrollingStyleInterface'
                    );
                }

                return $scrollingStyle;

            case 'string':
                if (!array_key_exists(strtolower($scrollingStyle), $this->scrollingTypes)) {
                    throw new Exception\InvalidArgumentException(
                        "Scrolling type '$scrollingStyle' is not supported, look for (" .
                        implode(',', array_keys($this->scrollingTypes)) .
                        ')'
                    );
                }
                $cls = $this->scrollingTypes[strtolower($scrollingStyle)];

                return new $cls();

            case 'null':
                // Fall through to default case

            default:
                throw new Exception\InvalidArgumentException(
                    'Scrolling style must be a class ' .
                    'name or object implementing Zend\Paginator\ScrollingStyle\ScrollingStyleInterface'
                );
        }
    }
}
