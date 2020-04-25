<?php

namespace Clue\React\Zlib;

use React\Stream\DuplexStreamInterface;
use Evenement\EventEmitter;
use React\Stream\WritableStreamInterface;
use React\Stream\Util;
use Exception;

/**
 * @internal Should not be relied upon outside of this package.
 */
class TransformStream extends EventEmitter implements DuplexStreamInterface
{
    private $readable = true;
    private $writable = true;
    private $closed = false;
    private $paused = false;
    private $drain = false;

    public function write($data)
    {
        if (!$this->writable || $data === '') {
            return $this->writable;
        }

        try {
            $this->transformData($data);

            if ($this->paused) {
                $this->drain = true;
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->emit('error', [$e]);
            $this->close();
            return false;
        }
    }

    public function end($data = null)
    {
        if (!$this->writable) {
            return;
        }
        $this->writable = false;

        try {
            if ($data === null) {
                $data = '';
            }
            $this->transformEnd($data);
        } catch (Exception $e) {
            $this->emit('error', [$e]);
            $this->close();
        }
    }

    public function close()
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;
        $this->readable = false;
        $this->writable = false;

        $this->emit('close');
        $this->removeAllListeners();
    }

    public function isReadable()
    {
        return $this->readable;
    }

    public function isWritable()
    {
        return $this->writable;
    }

    public function pause()
    {
        if (!$this->readable) {
            return;
        }

        $this->paused = true;
    }

    public function resume()
    {
        $this->paused = false;

        if ($this->drain && $this->writable) {
            $this->drain = false;
            $this->emit('drain');
        }
    }

    public function pipe(WritableStreamInterface $dest, array $options = array())
    {
        Util::pipe($this, $dest, $options);

        return $dest;
    }

    /**
     * can be overwritten in order to implement custom transformation behavior
     *
     * This gets passed a single chunk of $data and should emit a `data` event
     * with the filtered result.
     *
     * If the given data chunk is not valid, then you should throw an Exception
     * which will automatically be turned into an `error` event.
     *
     * If you do not overwrite this method, then its default implementation
     * simply emits a `data` event with the unmodified input data chunk.
     *
     * @param string $data
     */
    protected function transformData($data)
    {
        $this->emit('data', [$data]);
    }

    /**
     * can be overwritten in order to implement custom stream ending behavior
     *
     * This may get passed a single final chunk of $data and should emit an
     * `end` event and close the stream.
     *
     * If the given data chunk is not valid, then you should throw an Exception
     * which will automatically be turned into an `error` event.
     *
     * If you do not overwrite this method, then its default implementation simply
     * invokes `transformData()` on the unmodified input data chunk (if any),
     * which in turn defaults to emitting a `data` event and then finally
     * emits an `end` event and closes the stream.
     *
     * @param string $data
     * @see self::transformData()
     */
    protected function transformEnd($data)
    {
        if ($data !== '') {
            $this->transformData($data);
        }

        $this->emit('end');
        $this->close();
    }
}
