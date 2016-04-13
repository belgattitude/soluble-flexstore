<?php

namespace Soluble\FlexStore\Writer;

use Soluble\FlexStore\Writer\Http\SimpleHeaders;
use Soluble\FlexStore\Options;

class CSVWriter extends AbstractSendableWriter
{

    const SEPARATOR_TAB = "\t";
    const SEPARATOR_COMMA = ',';
    const SEPARATOR_SEMICOLON = ';';
    const SEPARATOR_NEWLINE_UNIX = "\n";
    const SEPARATOR_NEWLINE_WIN = "\r\n";

    /**
     *
     * @var SimpleHeaders
     */
    protected $headers;

    /**
     * @var array
     */
    protected $options = [
        'field_separator' => ";",
        'line_separator' => "\n",
        'enclosure' => '',
        'charset' => 'UTF-8',
        'escape' => '\\',
        // ignore charset transliteration errors
        'ignore_translit_error' => false
    ];

    /**
     *
     * @throws Exception\CharsetConversionException
     * @param Options $options
     * @return string csv encoded data
     */
    public function getData(Options $options = null)
    {
        if ($options === null) {
            // Take store global/default options
            $options = $this->store->getOptions();
        }


// TODO - TEST database connection charset !!!
//

        ini_set("default_charset", 'UTF-8');

        if (PHP_VERSION_ID < 50600) {
            iconv_set_encoding('internal_encoding', 'UTF-8');
        }

        /*
          $backup_encoding = iconv_get_encoding("internal_encoding");
          iconv_set_encoding("internal_encoding", "UTF-8");
          iconv_set_encoding("input_encoding", "UTF-8");
          iconv_set_encoding("output_encoding", "UTF-8");
          mb_internal_encoding("UTF-8");
         */

        $internal_encoding = strtoupper(ini_get('default_charset'));
        $charset = strtoupper($this->options['charset']);

        $csv = '';

        // Get unformatted data when using csv writer
        $options->getHydrationOptions()->disableFormatters();
        $data = $this->store->getData($options)->toArray();




        if (strtoupper($this->options['charset']) != $charset && !function_exists('iconv')) {
            throw new Exception\RuntimeException('CSV writer requires iconv extension');
        }

        $iconv_output_charset = $this->options['charset'];
        if ($this->options['ignore_translit_error']) {
            $iconv_output_charset .= "//TRANSLIT//IGNORE";
        }


        if (count($data) == 0) {
            $columns = $this->store->getColumnModel()->getColumns();
            $header_line = implode($this->options['field_separator'], array_keys((array) $columns));
            $csv .= $header_line . $this->options['line_separator'];
        } else {
            $header_line = implode($this->options['field_separator'], array_keys($data[0]));
            $csv .= $header_line . $this->options['line_separator'];


            foreach ($data as $row) {
                switch ($this->options['field_separator']) {
                    case self::SEPARATOR_TAB:
                        array_walk($row, [$this, 'escapeTabDelimiter']);
                        break;
                    default:
                        array_walk($row, [$this, 'escapeFieldDelimiter']);
                }

                array_walk($row, [$this, 'escapeLineDelimiter']);

                if ($this->options['enclosure'] != '') {
                    array_walk($row, [$this, 'addEnclosure']);
                }

                $line = implode($this->options['field_separator'], $row);


                if ($charset != $internal_encoding) {
                    $l = (string) $line;
                    if ($l != '') {
                        $l = @iconv($internal_encoding, $iconv_output_charset, $l);

                        if ($l === false) {
                            throw new Exception\CharsetConversionException("Cannot convert the charset to '" . $this->options['charset'] . "' from charset '$internal_encoding', value: '$line'.");
                        } else {
                            $line = $l;
                        }
                    }
                }

                $csv .= $line . $this->options['line_separator'];
            }
        }
        return $csv;
    }

    /**
     *
     * @param string $item
     * @return void
     */
    protected function escapeLineDelimiter(&$item)
    {
        $item = str_replace(self::SEPARATOR_NEWLINE_WIN, " ", $item);
        $item = str_replace(self::SEPARATOR_NEWLINE_UNIX, " ", $item);
    }

    /**
     *
     * @param string $item
     * @return void
     */
    protected function escapeTabDelimiter(&$item)
    {
        $item = str_replace("\t", " ", $item);
    }

    /**
     *
     * @param string $item
     * @return void
     */
    protected function escapeFieldDelimiter(&$item)
    {
        $item = str_replace($this->options['field_separator'], $this->options['escape'] . $this->options['field_separator'], $item);
    }

    /**
     *
     * @param string $item
     * @param string $key
     * @return void
     */
    protected function addEnclosure(&$item, $key)
    {
        $enc = $this->options['enclosure'];
        $escape = $this->options['escape'];
        if ($escape == '') {
            $item = $enc . str_replace($enc, '', $item) . $enc;
        } else {
            $item = $enc . str_replace($enc, $escape . $enc, $item) . $enc;
        }
    }

    /**
     * Return default headers for sending store data via http
     * @return SimpleHeaders
     */
    public function getHttpHeaders()
    {
        if ($this->headers === null) {
            $this->headers = new SimpleHeaders();
            $this->headers->setContentType('text/csv', $this->options['charset']);
            //$this->headers->setContentDispositionType(SimpleHeaders::DIPOSITION_ATTACHEMENT);
        }
        return $this->headers;
    }
}
