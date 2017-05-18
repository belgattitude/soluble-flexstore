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

namespace Soluble\FlexStore\Writer;

use Soluble\FlexStore\Writer\Http\SimpleHeaders;

interface HttpSendableInterface
{
    /**
     * Return (default) headers for sending store data via http.
     *
     * @return \Soluble\FlexStore\Writer\Http\SimpleHeaders
     */
    public function getHttpHeaders();

    /**
     * Send the store data via http.
     *
     * @param SimpleHeaders $headers
     * @param bool          $die_after
     */
    public function send(SimpleHeaders $headers = null, $die_after = true);
}
