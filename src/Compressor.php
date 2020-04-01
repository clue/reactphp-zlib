<?php

namespace Clue\React\Zlib;

use Clue\StreamFilter as Filter;

/**
 * The `Compressor` class can be used to compress a stream of data.
 *
 * It implements the [`DuplexStreamInterface`](https://github.com/reactphp/stream#duplexstreaminterface)
 * and accepts uncompressed data on its writable side and emits compressed data
 * on its readable side.
 *
 * ```php
 * $encoding = ZLIB_ENCODING_GZIP; // or ZLIB_ENCODING_RAW or ZLIB_ENCODING_DEFLATE
 * $compressor = new Clue\React\Zlib\Compressor($encoding);
 *
 * $compressor->on('data', function ($data) {
 *     echo $data; // compressed binary data chunk
 * });
 *
 * $compressor->write($uncompressed); // write uncompressed data chunk
 * ```
 *
 * This is particularly useful in a piping context:
 *
 * ```php
 * $input->pipe($filterBadWords)->pipe($compressor)->pipe($output);
 * ```
 *
 * For more details, see ReactPHP's
 * [`DuplexStreamInterface`](https://github.com/reactphp/stream#duplexstreaminterface).
 *
 * >   Internally, it implements the deprecated `ZlibFilterStream` class only for
 *     BC reasons. For best forwards compatibility, you should only rely on it
 *     implementing the `DuplexStreamInterface`.
 */
final class Compressor extends ZlibFilterStream
{
    /**
     * @param int $encoding ZLIB_ENCODING_GZIP, ZLIB_ENCODING_RAW or ZLIB_ENCODING_DEFLATE
     * @param int $level    optional compression level
     */
    public function __construct($encoding, $level = -1)
    {
        parent::__construct(
            Filter\fun('zlib.deflate', array('window' => $encoding, 'level' => $level))
        );
    }
}
