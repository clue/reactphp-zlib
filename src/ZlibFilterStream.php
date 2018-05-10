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
    /**
     * @param int $encoding ZLIB_ENCODING_GZIP, ZLIB_ENCODING_RAW or ZLIB_ENCODING_DEFLATE
     * @param int $level    optional compression level
     * @return self
     */
    public static function createCompressor($encoding, $level = -1)
    {
        return new self(
            Filter\fun('zlib.deflate', array('window' => $encoding, 'level' => $level))
        );
    }

    /**
     * @param int $encoding ZLIB_ENCODING_GZIP, ZLIB_ENCODING_RAW or ZLIB_ENCODING_DEFLATE
     * @return self
     */
    public static function createDecompressor($encoding)
    {
        return new self(
            Filter\fun('zlib.inflate', array('window' => $encoding))
        );
    }

    /**
     * @deprecated
     * @return self
     */
    public static function createGzipCompressor($level = -1)
    {
        return self::createCompressor(15 | 16 /* ZLIB_ENCODING_GZIP */, $level);
    }

    /**
     * @deprecated
     * @return self
     */
    public static function createGzipDecompressor()
    {
        return self::createDecompressor(15 | 16 /* ZLIB_ENCODING_GZIP */);
    }

    /**
     * @deprecated
     * @return self
     */
    public static function createDeflateCompressor($level = -1)
    {
        return self::createCompressor(-15 /* ZLIB_ENCODING_RAW */, $level);
    }

    /**
     * @deprecated
     * @return self
     */
    public static function createDeflateDecompressor()
    {
        return self::createDecompressor(-15 /* ZLIB_ENCODING_RAW */);
    }

    /**
     * @deprecated
     * @return self
     */
    public static function createZlibCompressor($level = -1)
    {
        return self::createCompressor(15 /* ZLIB_ENCODING_DEFLATE */, $level);
    }

    /**
     * @deprecated
     * @return self
     */
    public static function createZlibDecompressor()
    {
        return self::createDecompressor(15 /* ZLIB_ENCODING_DEFLATE */);
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
