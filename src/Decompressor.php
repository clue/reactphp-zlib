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
        $context = @inflate_init($encoding);
        if ($context === false) {
            throw new \InvalidArgumentException('Unable to initialize decompressor' . strstr(error_get_last()['message'], ':'));
        }

        $this->context = $context;
    }

    protected function transformData($chunk)
    {
        $ret = @inflate_add($this->context, $chunk);
        if ($ret === false) {
            throw new \RuntimeException('Unable to decompress' . strstr(error_get_last()['message'], ':'));
        }

        if ($ret !== '') {
            $this->emit('data', [$ret]);
        }
    }

    protected function transformEnd($chunk)
    {
        $ret = @inflate_add($this->context, $chunk, ZLIB_FINISH);
        $this->context = null;

        if ($ret === false) {
            throw new \RuntimeException('Unable to decompress' . strstr(error_get_last()['message'], ':'));
        }

        if ($ret !== '') {
            $this->emit('data', [$ret]);
        }

        $this->emit('end');
        $this->close();
    }
}
