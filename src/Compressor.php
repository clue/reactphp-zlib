<?php

namespace Clue\React\Zlib;

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
 * This class takes an optional `int $level` parameter that controls the
 * compression level from `0` (no compression) to `9` (best compression).
 * It defaults to `-1` which uses zlib's default compression level.
 *
 * This class takes an optional `int $flush` parameter that controls when
 * compressed data will be emitted. It defaults to `ZLIB_NO_FLUSH` which
 * buffers data internally to achieve the best compression ratio, so compressed
 * data is only emitted once enough data is available or the stream ends.
 * For streaming protocols like EventSource (SSE), you can use `ZLIB_SYNC_FLUSH`
 * to make sure each chunk can be decompressed by the receiving side immediately
 * after writing. Each flush adds a few bytes, so this may produce larger output
 * when writing many small chunks. This parameter also accepts `ZLIB_PARTIAL_FLUSH`
 * and `ZLIB_FULL_FLUSH`, the latter also resets the compression state for each
 * chunk, which may considerably increase output size.
 *
 * ```php
 * $compressor = new Clue\React\Zlib\Compressor(ZLIB_ENCODING_GZIP, -1, ZLIB_SYNC_FLUSH);
 * ```
 *
 * For more details, see ReactPHP's
 * [`DuplexStreamInterface`](https://github.com/reactphp/stream#duplexstreaminterface).
 */
final class Compressor extends TransformStream
{
    /** @var ?resource */
    private $context;

    /** @var int */
    private $flush;

    /**
     * @param int $encoding ZLIB_ENCODING_GZIP, ZLIB_ENCODING_RAW or ZLIB_ENCODING_DEFLATE
     * @param int $level    optional compression level
     * @param int $flush    optional flush mode (ZLIB_NO_FLUSH, ZLIB_PARTIAL_FLUSH, ZLIB_SYNC_FLUSH, ZLIB_FULL_FLUSH)
     */
    public function __construct($encoding, $level = -1, int $flush = ZLIB_NO_FLUSH)
    {
        $errstr = '';
        set_error_handler(function ($_, $error) use (&$errstr) {
            // Match errstr from PHP's warning message.
            // inflate_init(): encoding mode must be ZLIB_ENCODING_RAW, ZLIB_ENCODING_GZIP or ZLIB_ENCODING_DEFLATE
            $errstr = strstr($error, ':'); // @codeCoverageIgnore
        });

        try {
            $context = deflate_init($encoding, ['level' => $level]);
        } catch (\ValueError $e) { // @codeCoverageIgnoreStart
            // Throws ValueError on PHP 8.0+
            restore_error_handler();
            throw $e;
        } // @codeCoverageIgnoreEnd

        restore_error_handler();

        if ($context === false) {
            throw new \InvalidArgumentException('Unable to initialize compressor' . $errstr); // @codeCoverageIgnore
        }

        if (!in_array($flush, [ZLIB_NO_FLUSH, ZLIB_PARTIAL_FLUSH, ZLIB_SYNC_FLUSH, ZLIB_FULL_FLUSH], true)) {
            throw new \InvalidArgumentException('Argument #3 ($flush) must be one of ZLIB_NO_FLUSH, ZLIB_PARTIAL_FLUSH, ZLIB_SYNC_FLUSH or ZLIB_FULL_FLUSH');
        }

        $this->context = $context;
        $this->flush = $flush;
    }

    protected function transformData($chunk)
    {
        $ret = deflate_add($this->context, $chunk, $this->flush);

        if ($ret !== '') {
            $this->emit('data', [$ret]);
        }
    }

    protected function transformEnd($chunk)
    {
        $ret = deflate_add($this->context, $chunk, ZLIB_FINISH);
        $this->context = null;

        if ($ret !== '') {
            $this->emit('data', [$ret]);
        }

        $this->emit('end');
        $this->close();
    }
}
