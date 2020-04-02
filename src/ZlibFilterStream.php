<?php

namespace Clue\React\Zlib;

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
 * @deprecated 0.2.2 External usage of `ZlibFilterStream` is deprecated, use `Compressor` or `Decompressor` instead.
 * @see Compressor
 * @see Decompressor
 */
class ZlibFilterStream extends TransformStream
{
    /**
     * @deprecated
     * @return self
     */
    public static function createGzipCompressor($level = -1)
    {
        return new Compressor(15 | 16 /* ZLIB_ENCODING_GZIP */, $level);
    }

    /**
     * @deprecated
     * @return self
     */
    public static function createGzipDecompressor()
    {
        return new Decompressor(15 | 16 /* ZLIB_ENCODING_GZIP */);
    }

    /**
     * @deprecated
     * @return self
     */
    public static function createDeflateCompressor($level = -1)
    {
        return new Compressor(-15 /* ZLIB_ENCODING_RAW */, $level);
    }

    /**
     * @deprecated
     * @return self
     */
    public static function createDeflateDecompressor()
    {
        return new Decompressor(-15 /* ZLIB_ENCODING_RAW */);
    }

    /**
     * @deprecated
     * @return self
     */
    public static function createZlibCompressor($level = -1)
    {
        return new Compressor(15 /* ZLIB_ENCODING_DEFLATE */, $level);
    }

    /**
     * @deprecated
     * @return self
     */
    public static function createZlibDecompressor()
    {
        return new Decompressor(15 /* ZLIB_ENCODING_DEFLATE */);
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
