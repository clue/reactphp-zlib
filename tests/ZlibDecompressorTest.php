<?php

use Clue\React\Zlib\Decompressor;

class ZlibDecompressorTest extends TestCase
{
    private $decompressor;

    public function setUp()
    {
        $this->decompressor = new Decompressor(ZLIB_ENCODING_DEFLATE);
    }

    public function testDecompressEmpty()
    {
        $this->decompressor->on('data', $this->expectCallableNever());
        $this->decompressor->on('end', $this->expectCallableOnce());

        $this->decompressor->end(gzcompress(''));
    }

    public function testDecompressHelloWorld()
    {
        $this->decompressor->on('data', function ($data) use (&$buffered) {
            $buffered .= $data;
        });
        $this->decompressor->on('end', $this->expectCallableOnce());

        $this->decompressor->end(gzcompress('hello world'));

        $this->assertEquals('hello world', $buffered);
    }

    public function testDecompressBig()
    {
        $this->decompressor->on('data', function ($data) use (&$buffered) {
            $buffered .= $data;
        });
        $this->decompressor->on('end', $this->expectCallableOnce());

        $data = str_repeat('hello', 100);
        $bytes = gzcompress($data);
        foreach (str_split($bytes, 1) as $byte) {
            $this->decompressor->write($byte);
        }
        $this->decompressor->end();

        $this->assertEquals($data, $buffered);
    }

    public function testDecompressInvalid()
    {
        $this->markTestSkipped('Not supported by any PHP version (neither does reject invalid data)');

        $this->decompressor->on('data', $this->expectCallableNever());
        $this->decompressor->on('error', $this->expectCallableOnce());

        $this->decompressor->end('invalid');
    }
}
