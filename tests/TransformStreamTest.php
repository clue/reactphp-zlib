<?php

use Clue\React\Zlib\TransformStream;
use React\Stream\ThroughStream;

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

    public function testCloseRemovesListeners()
    {
        $stream = new TransformStream();
        $this->assertCount(0, $stream->listeners('close'));

        $stream->on('close', $this->expectCallableOnce());
        $this->assertCount(1, $stream->listeners('close'));

        $stream->close();
        $this->assertCount(0, $stream->listeners('close'));
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

    public function testWriteReturnsTrueNormally()
    {
        $stream = new TransformStream();

        $ret = $stream->write('hello');
        $this->assertTrue($ret);
    }

    public function testWriteEmptyStringReturnsTrueNormally()
    {
        $stream = new TransformStream();

        $ret = $stream->write('');
        $this->assertTrue($ret);
    }

    public function testWriteReturnsFalseWhenClosed()
    {
        $stream = new TransformStream();
        $stream->close();

        $ret = $stream->write('hello');
        $this->assertFalse($ret);
    }

    public function testWriteEmptyStringReturnsFalseWhenClosed()
    {
        $stream = new TransformStream();
        $stream->close();

        $ret = $stream->write('');
        $this->assertFalse($ret);
    }

    public function testWriteReturnsFalseWhenPaused()
    {
        $stream = new TransformStream();
        $stream->pause();

        $ret = $stream->write('hello');
        $this->assertFalse($ret);
    }

    public function testWriteReturnsTrueWhenResumedAgain()
    {
        $stream = new TransformStream();
        $stream->pause();
        $stream->resume();

        $ret = $stream->write('hello');
        $this->assertTrue($ret);
    }

    public function testResumeEmitsDrainEventWhenPreviousWriteReturnedFalse()
    {
        $stream = new TransformStream();
        $stream->pause();
        $stream->write('hello');

        $stream->on('drain', $this->expectCallableOnce());
        $stream->resume();
    }

    public function testResumeDoesNotEmitDrainEventWhenNoPreviousWriteReturnedFalse()
    {
        $stream = new TransformStream();
        $stream->pause();

        $stream->on('drain', $this->expectCallableNever());
        $stream->resume();
    }

    public function testPauseAndResumeIsNoOpWhenClosed()
    {
        $stream = new TransformStream();
        $stream->close();

        $stream->on('drain', $this->expectCallableNever());
        $stream->pause();
        $stream->resume();
    }

    public function testSupportsBackPressureInPipeChain()
    {
        $source = new ThroughStream();

        $dest = new ThroughStream();
        $dest->pause();

        $stream = new TransformStream();

        $source->pipe($stream)->pipe($dest);

        $ret = $source->write('hello');
        $this->assertFalse($ret);
    }
}
