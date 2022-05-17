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
 */
final class Compressor extends TransformStream
{
    /** @var ?resource */
    private $context;

    /**
     * @param int $encoding ZLIB_ENCODING_GZIP, ZLIB_ENCODING_RAW or ZLIB_ENCODING_DEFLATE
     * @param int $level    optional compression level
     */
    public function __construct($encoding, $level = -1)
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

        $this->context = $context;
    }

    protected function transformData($chunk)
    {
        $ret = deflate_add($this->context, $chunk, ZLIB_NO_FLUSH);

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
