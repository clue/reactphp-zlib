<?php

use Clue\React\Zlib\ZlibFilterStream;

class ZlibFilterGzipDecompressorTest extends TestCase
{
    private $decompressor;

    public function setUp()
    {
        if (defined('HHVM_VERSION')) $this->markTestSkipped('Not supported on HHVM (ignores window size / encoding format)');

        $this->decompressor = ZlibFilterStream::createGzipDecompressor();
    }

    public function testDecompressEmpty()
    {
        $this->decompressor->on('data', $this->expectCallableNever());
        $this->decompressor->on('end', $this->expectCallableOnce());

        $this->decompressor->end(gzencode(''));
    }

    public function testDecompressHelloWorld()
    {
        $this->decompressor->on('data', function ($data) use (&$buffered) {
            $buffered .= $data;
        });
        $this->decompressor->on('end', $this->expectCallableOnce());

        $this->decompressor->end(gzencode('hello world'));

        $this->assertEquals('hello world', $buffered);
    }

    public function testDecompressBig()
    {
        $this->decompressor->on('data', function ($data) use (&$buffered) {
            $buffered .= $data;
        });
        $this->decompressor->on('end', $this->expectCallableOnce());

        $data = str_repeat('hello', 100);
        $bytes = gzencode($data);
        foreach (str_split($bytes, 1) as $byte) {
            $this->decompressor->write($byte);
        }
        $this->decompressor->end();

        $this->assertEquals($data, $buffered);
    }

    public function testDecompressInvalid()
    {
        $this->markTestSkipped('Not supported by any PHP engine (neither does reject invalid data)');

        $this->decompressor->on('data', $this->expectCallableNever());
        $this->decompressor->on('error', $this->expectCallableOnce());

        $this->decompressor->end('invalid');
    }
}
