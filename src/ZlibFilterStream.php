<?php

namespace Clue\React\Zlib;

use Clue\StreamFilter as Filter;

/**
 * Compressor and decompressor using PHP's zlib compression filters.
 *
 * Supports the following compression formats:
 *
 * RFC 1952 (GZIP compressed format)
 * RFC 1951 (raw DEFLATE compressed format)
 * RFC 1950 (ZLIB compressed format)
 *
 * @link http://php.net/manual/en/filters.compression.php
 */
class ZlibFilterStream extends TransformStream
{
    public static function createGzipCompressor($level = -1)
    {
        return new self(
            Filter\fun('zlib.deflate', array('window' => 15|16, 'level' => $level))
        );
    }

    public static function createGzipDecompressor()
    {
        return new self(
            Filter\fun('zlib.inflate', array('window' => 15|16))
        );
    }

    public static function createDeflateCompressor($level = -1)
    {
        return new self(
            Filter\fun('zlib.deflate', array('window' => -15, 'level' => $level))
        );
    }

    public static function createDeflateDecompressor()
    {
        return new self(
            Filter\fun('zlib.inflate', array('window' => -15))
        );
    }

    public static function createZlibCompressor($level = -1)
    {
        return new self(
            Filter\fun('zlib.deflate', array('window' => 15, 'level' => $level))
        );
    }

    public static function createZlibDecompressor()
    {
        return new self(
            Filter\fun('zlib.inflate', array('window' => 15))
        );
    }

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
