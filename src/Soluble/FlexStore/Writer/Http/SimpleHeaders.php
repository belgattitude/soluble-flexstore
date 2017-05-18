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

namespace Soluble\FlexStore\Writer\Http;

class SimpleHeaders
{
    const DIPOSITION_INLINE = 'inline';
    const DIPOSITION_ATTACHEMENT = 'attachement';

    /**
     * Supported disposition types.
     *
     * @var array
     */
    protected $disposition_types = ['inline', 'attachement'];

    /**
     * @var array
     */
    protected $default_params = [
        'content-type' => null,
        'content-type-charset' => null,
        'content-disposition-filename' => null,
        'content-disposition-type' => null,
        'content-length' => null
    ];

    /**
     * @var array
     */
    protected $params;

    public function __construct()
    {
        $this->params = $this->default_params;
    }

    /**
     * @param string $content_type
     * @param string $charset
     *
     * @return SimpleHeaders
     */
    public function setContentType($content_type, $charset = null)
    {
        $this->params['content-type'] = $content_type;
        if ($charset !== null) {
            $this->setCharset($charset);
        }

        return $this;
    }

    public function getContentType()
    {
        return $this->params['content-type'];
    }

    /**
     * Set the content disposition filename and type.
     *
     * @param string $filename                 filename when downloading
     * @param string $content_disposition_type by default attachment
     *
     * @return SimpleHeaders
     */
    public function setFilename($filename, $content_disposition_type = 'attachement')
    {
        $this->params['content-disposition-filename'] = $filename;
        $this->setContentDispositionType($content_disposition_type);

        return $this;
    }

    /**
     * Return the content disposition filename.
     *
     * @return string|null
     */
    public function getFilename()
    {
        return $this->params['content-disposition-filename'];
    }

    /**
     * Set the content type charset.
     *
     * @throws Exception\RuntimeException
     *
     * @param string $charset
     *
     * @return SimpleHeaders
     */
    public function setCharset($charset)
    {
        if ($this->getContentType() == '') {
            throw new Exception\RuntimeException(__METHOD__ . ' Content-type must be specified prior to setting charset');
        }
        $this->params['content-type-charset'] = $charset;

        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->params['content-type-charset'];
    }

    /**
     * Set the preferred content disposition type 'attachement' or 'inline'.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param string $content_disposition_type
     *
     * @return SimpleHeaders
     */
    public function setContentDispositionType($content_disposition_type)
    {
        if (!in_array($content_disposition_type, $this->disposition_types, true)) {
            $supported = implode(',', $this->disposition_types);
            throw new Exception\InvalidArgumentException(__METHOD__ . " Content disposition type '$content_disposition_type' not in supported types: $supported");
        }

        $this->params['content-disposition-type'] = $content_disposition_type;

        return $this;
    }

    /**
     * Return the content disposition type.
     *
     * @return string
     */
    public function getContentDispositionType()
    {
        return $this->params['content-disposition-type'];
    }

    /**
     * @param int $length
     *
     * @return SimpleHeaders
     */
    public function setContentLength($length)
    {
        $this->params['content-length'] = $length;

        return $this;
    }

    /**
     * @return int
     */
    public function getContentLength()
    {
        return $this->params['content-length'];
    }

    public function getHeaderLines()
    {
        $lines = [];
        if ($this->params['content-type'] !== null) {
            $ct = 'Content-Type: ' . $this->params['content-type'];
            if ($this->params['content-type-charset'] !== null) {
                $ct .= '; charset=' . $this->params['content-type-charset'];
            }
            $lines[] = $ct;
        }

        if ($this->params['content-disposition-type'] !== null) {
            $cd = 'Content-Disposition: ' . $this->params['content-disposition-type'];
            if ($this->params['content-disposition-filename'] !== null) {
                $cd .= '; filename="' . $this->params['content-disposition-filename'] . '"';
            }
            $lines[] = $cd;
        }

        if ($this->params['content-length'] !== null) {
            $lines[] = 'Content-Length: ' . $this->params['content-length'];
        }

        return $lines;
    }

    /**
     * Output the headers (php).
     *
     * @param bool $replace tells to replace eventual headers
     */
    public function outputHeaders($replace = true)
    {
        $lines = $this->getHeaderLines();
        foreach ($lines as $line) {
            header($line, $replace);
        }
    }
}
