<?php

namespace Clue\Tests\React\Zlib;

use Clue\React\Zlib\Decompressor;

class DeflateDecompressorTest extends TestCase
{
    private $decompressor;

    /**
     * @before
     */
    public function setUpDecompressor()
    {
        $this->decompressor = new Decompressor(ZLIB_ENCODING_RAW);
    }

    public function testInflateEmpty()
    {
        $this->decompressor->on('data', $this->expectCallableNever());
        $this->decompressor->on('end', $this->expectCallableOnce());

        $this->decompressor->end(gzdeflate(''));
    }

    public function testInflateHelloWorld()
    {
        $this->decompressor->on('data', function ($data) use (&$buffered) {
            $buffered .= $data;
        });
        $this->decompressor->on('end', $this->expectCallableOnce());

        $this->decompressor->end(gzdeflate('hello world'));

        $this->assertEquals('hello world', $buffered);
    }

    public function testInflateBig()
    {
        $this->decompressor->on('data', function ($data) use (&$buffered) {
            $buffered .= $data;
        });
        $this->decompressor->on('end', $this->expectCallableOnce());

        $data = str_repeat('hello', 100);
        $bytes = gzdeflate($data);
        foreach (str_split($bytes, 1) as $byte) {
            $this->decompressor->write($byte);
        }
        $this->decompressor->end();

        $this->assertEquals($data, $buffered);
    }

    public function testDecompressInvalidDataEmitsErrorWithoutCallingCustomErrorHandler()
    {
        $this->decompressor->on('data', $this->expectCallableNever());
        $this->decompressor->on('error', $this->expectCallableOnce());

        $error = null;
        set_error_handler(function ($_, $errstr) use (&$error) {
            $error = $errstr;
        });

        $this->decompressor->write('invalid');

        restore_error_handler();
        $this->assertNull($error);
    }

    public function testDecompressInvalidOnEndEmitsErrorWithoutCallingCustomErrorHandler()
    {
        $this->decompressor->on('data', $this->expectCallableNever());
        $this->decompressor->on('error', $this->expectCallableOnce());

        $error = null;
        set_error_handler(function ($_, $errstr) use (&$error) {
            $error = $errstr;
        });

        $this->decompressor->end('invalid');

        restore_error_handler();
        $this->assertNull($error);
    }
}
