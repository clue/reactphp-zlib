<?php

namespace Clue\React\Zlib;

use React\Stream\DuplexStreamInterface;
use Evenement\EventEmitter;
use React\Stream\WritableStreamInterface;
use React\Stream\Util;
use Exception;

/**
 * @internal Should not be relied upon outside of this package. Should eventually be moved to react/stream?
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
            $this->forwardError($e);
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
            $this->forwardError($e);
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
     * Forwards a single "data" event to the reading side of the stream
     *
     * This will emit an "data" event.
     *
     * If the stream is not readable, then this is a NO-OP.
     *
     * @param string $data
     */
    protected function forwardData($data)
    {
        if (!$this->readable) {
            return;
        }
        $this->emit('data', array($data));
    }

    /**
     * Forwards an "end" event to the reading side of the stream
     *
     * This will emit an "end" event and will then close this stream.
     *
     * If the stream is not readable, then this is a NO-OP.
     *
     * @uses self::close()
     */
    protected function forwardEnd()
    {
        if (!$this->readable) {
            return;
        }
        $this->readable = false;
        $this->writable = false;

        $this->emit('end');
        $this->close();
    }

    /**
     * Forwards the given $error message to the reading side of the stream
     *
     * This will emit an "error" event and will then close this stream.
     *
     * If the stream is not readable, then this is a NO-OP.
     *
     * @param Exception $error
     * @uses self::close()
     */
    protected function forwardError(Exception $error)
    {
        if (!$this->readable) {
            return;
        }
        $this->readable = false;
        $this->writable = false;

        $this->emit('error', array($error));
        $this->close();
    }

    /**
     * can be overwritten in order to implement custom transformation behavior
     *
     * This gets passed a single chunk of $data and should invoke `forwardData()`
     * with the filtered result.
     *
     * If the given data chunk is not valid, then you should invoke `forwardError()`
     * or throw an Exception.
     *
     * If you do not overwrite this method, then its default implementation simply
     * invokes `forwardData()` on the unmodified input data chunk.
     *
     * @param string $data
     * @see self::forwardData()
     */
    protected function transformData($data)
    {
        $this->forwardData($data);
    }

    /**
     * can be overwritten in order to implement custom stream ending behavior
     *
     * This may get passed a single final chunk of $data and should invoke `forwardEnd()`.
     *
     * If the given data chunk is not valid, then you should invoke `forwardError()`
     * or throw an Exception.
     *
     * If you do not overwrite this method, then its default implementation simply
     * invokes `transformData()` on the unmodified input data chunk (if any),
     * which in turn defaults to invoking `forwardData()` and then finally
     * invokes `forwardEnd()`.
     *
     * @param string $data
     * @see self::transformData()
     * @see self::forwardData()
     * @see self::forwardEnd()
     */
    protected function transformEnd($data)
    {
        if ($data !== '') {
            $this->transformData($data);
        }
        $this->forwardEnd();
    }
}
