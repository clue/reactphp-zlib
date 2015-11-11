<?php

use Clue\React\Zlib\TransformStream;

class TransformStreamTest extends TestCase
{
    public function testForwardsDataAndEndClosesStream()
    {
        $stream = new TransformStream();

        $stream->on('data', function ($chunk) use (&$buffered) {
            $buffered .= $chunk;
        });
        $stream->on('end', $this->expectCallableOnce());
        $stream->on('close', $this->expectCallableOnce());

        $stream->on('error', $this->expectCallableNever());

        $stream->write('hello');
        $stream->end('world');

        $this->assertEquals('helloworld', $buffered);
    }

    public function testEndWithNoDataEmitsEndAndClose()
    {
        $stream = new TransformStream();

        $stream->on('end', $this->expectCallableOnce());
        $stream->on('close', $this->expectCallableOnce());

        $stream->on('data', $this->expectCallableNever());
        $stream->on('error', $this->expectCallableNever());

        $stream->end();

        return $stream;
    }

    /**
     * @depends testEndWithNoDataEmitsEndAndClose
     * @param TransformStream $stream
     */
    public function testDoesNotEmitIfAlreadyEnded(TransformStream $stream)
    {
        $stream->on('data', $this->expectCallableNever());
        $stream->on('error', $this->expectCallableNever());
        $stream->on('end', $this->expectCallableNever());
        $stream->on('close', $this->expectCallableNever());

        $stream->write('hello');
        $stream->end();
        $stream->close();
    }

    public function testCloseOnlyEmitsCloses()
    {
        $stream = new TransformStream();

        $stream->on('close', $this->expectCallableOnce());

        $stream->on('data', $this->expectCallableNever());
        $stream->on('end', $this->expectCallableNever());
        $stream->on('error', $this->expectCallableNever());

        $stream->close();

        return $stream;
    }

    /**
     * @depends testCloseOnlyEmitsCloses
     * @param TransformStream $stream
     */
    public function testDoesNotEmitIfAlreadyClosed(TransformStream $stream)
    {
        $stream->on('data', $this->expectCallableNever());
        $stream->on('error', $this->expectCallableNever());
        $stream->on('end', $this->expectCallableNever());
        $stream->on('close', $this->expectCallableNever());

        $stream->write('hello');
        $stream->end();
        $stream->close();
    }
}
