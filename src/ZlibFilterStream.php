<?php

namespace Clue\React\Zlib;

/**
 * [Internal] Compressor and decompressor using PHP's zlib compression filters.
 *
 * Supports the following compression formats:
 *
 * RFC 1952 (GZIP compressed format)
 * RFC 1951 (raw DEFLATE compressed format)
 * RFC 1950 (ZLIB compressed format)
 *
 * @internal Should not be relied upon outside of this package.
 * @link http://php.net/manual/en/filters.compression.php
 * @see Compressor
 * @see Decompressor
 */
class ZlibFilterStream extends TransformStream
{
    private $filter;

    /**
     * @var int|null
     * @see Compressor
     * @internal
     */
    protected $emptyWrite;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    protected function transformData($chunk)
    {
        $filter = $this->filter;
        $ret = $filter($chunk);

        if ($ret !== '') {
            $this->emptyWrite = null;
            $this->forwardData($ret);
        }
    }

    protected function transformEnd($chunk)
    {
        $filter = $this->filter;
        $ret = $filter($chunk) . $filter();

        // Stream ends successfully and did not emit any data whatsoever?
        // This happens when compressing an empty stream with PHP 7 only.
        // Bypass filter and manually compress/encode empty string.
        if ($this->emptyWrite !== null && $ret === '') {
            $ret = \zlib_encode('', $this->emptyWrite);
        }

        if ($ret !== '') {
            $this->forwardData($ret);
        }

        $this->forwardEnd();
        $this->filter = null;
    }
}
