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

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    protected function transformData($chunk)
    {
        $filter = $this->filter;
        $ret = $filter($chunk);

        if ($ret !== '') {
            $this->forwardData($ret);
        }
    }

    protected function transformEnd($chunk)
    {
        $filter = $this->filter;
        $ret = $filter($chunk) . $filter();

        if ($ret !== '') {
            $this->forwardData($ret);
        }

        $this->forwardEnd();
        $this->filter = null;
    }
}
