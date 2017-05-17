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

namespace Soluble\FlexStore\Writer;

use Soluble\FlexStore\Writer\Http\SimpleHeaders;

abstract class AbstractSendableWriter extends AbstractWriter implements HttpSendableInterface
{
    /**
     * Return (default) headers for sending store data via http.
     *
     * @return \Soluble\FlexStore\Writer\Http\SimpleHeaders
     */
    abstract public function getHttpHeaders();

    /**
     * Send the store data via http.
     *
     * @throws \Exception if error occurs in getData
     *
     * @param SimpleHeaders $headers
     * @param bool          $die_after
     */
    public function send(SimpleHeaders $headers = null, $die_after = true)
    {
        if ($headers === null) {
            $headers = $this->getHttpHeaders();
        }

        ob_end_clean();

        try {
            $data = $this->getData();
        } catch (\Exception $e) {
            throw $e;
        }
        $headers->outputHeaders($replace = true);
        echo $data;
        if ($die_after) {
            die();
        }
    }
}
