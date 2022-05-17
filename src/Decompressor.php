<?php

namespace Clue\React\Zlib;

use Clue\StreamFilter as Filter;

/**
 * The `Decompressor` class can be used to decompress a stream of data.
 *
 * It implements the [`DuplexStreamInterface`](https://github.com/reactphp/stream#duplexstreaminterface)
 * and accepts compressed data on its writable side and emits decompressed data
 * on its readable side.
 *
 * ```php
 * $encoding = ZLIB_ENCODING_GZIP; // or ZLIB_ENCODING_RAW or ZLIB_ENCODING_DEFLATE
 * $decompressor = new Clue\React\Zlib\Decompressor($encoding);
 *
 * $decompressor->on('data', function ($data) {
 *     echo $data; // decompressed data chunk
 * });
 *
 * $decompressor->write($compressed); // write compressed binary data chunk
 * ```
 *
 * This is particularly useful in a piping context:
 *
 * ```php
 * $input->pipe($decompressor)->pipe($filterBadWords)->pipe($output);
 * ```
 *
 * For more details, see ReactPHP's
 * [`DuplexStreamInterface`](https://github.com/reactphp/stream#duplexstreaminterface).
 */
final class Decompressor extends TransformStream
{
    /** @var ?resource */
    private $context;

    /**
     * @param int $encoding ZLIB_ENCODING_GZIP, ZLIB_ENCODING_RAW or ZLIB_ENCODING_DEFLATE
     */
    public function __construct($encoding)
    {
        $errstr = '';
        set_error_handler(function ($_, $error) use (&$errstr) {
            // Match errstr from PHP's warning message.
            // inflate_init(): encoding mode must be ZLIB_ENCODING_RAW, ZLIB_ENCODING_GZIP or ZLIB_ENCODING_DEFLATE
            $errstr = strstr($error, ':'); // @codeCoverageIgnore
        });

        try {
            $context = inflate_init($encoding);
        } catch (\ValueError $e) { // @codeCoverageIgnoreStart
            // Throws ValueError on PHP 8.0+
            restore_error_handler();
            throw $e;
        } // @codeCoverageIgnoreEnd

        restore_error_handler();

        if ($context === false) {
            throw new \InvalidArgumentException('Unable to initialize decompressor' . $errstr); // @codeCoverageIgnore
        }

        $this->context = $context;
    }

    protected function transformData($chunk)
    {
        $errstr = '';
        set_error_handler(function ($_, $error) use (&$errstr) {
            // Match errstr from PHP's warning message.
            // inflate_add(): data error
            $errstr = strstr($error, ':');
        });

        $ret = inflate_add($this->context, $chunk);

        restore_error_handler();

        if ($ret === false) {
            throw new \RuntimeException('Unable to decompress' . $errstr);
        }

        if ($ret !== '') {
            $this->emit('data', [$ret]);
        }
    }

    protected function transformEnd($chunk)
    {
        $errstr = '';
        set_error_handler(function ($_, $error) use (&$errstr) {
            // Match errstr from PHP's warning message.
            // inflate_add(): data error
            $errstr = strstr($error, ':');
        });

        $ret = inflate_add($this->context, $chunk, ZLIB_FINISH);
        $this->context = null;

        restore_error_handler();

        if ($ret === false) {
            throw new \RuntimeException('Unable to decompress' . $errstr);
        }

        if ($ret !== '') {
            $this->emit('data', [$ret]);
        }

        $this->emit('end');
        $this->close();
    }
}
