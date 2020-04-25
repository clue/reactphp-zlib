<?php

namespace Clue\Tests\React\Zlib;

use Clue\React\Zlib\Compressor;

class ZlibCompressorTest extends TestCase
{
    private $compressor;

    public function setUp()
    {
        $this->compressor = new Compressor(ZLIB_ENCODING_DEFLATE);
    }

    public function testCompressEmpty()
    {
        $this->compressor->on('data', $this->expectCallableOnceWith("\x78\x9c" . "\x03\x00" . "\x00\x00\x00\x01"));
        $this->compressor->on('end', $this->expectCallableOnce());

        $this->compressor->end();
    }

    public function testCompressHelloWorld()
    {
        $this->compressor->on('data', function ($data) use (&$buffered) {
            $buffered .= $data;
        });
        $this->compressor->on('end', $this->expectCallableOnce());

        $this->compressor->end('hello world');

        $this->assertEquals('hello world', gzuncompress($buffered));
    }

    public function testCompressBig()
    {
        $this->compressor->on('data', function ($data) use (&$buffered) {
            $buffered .= $data;
        });
        $this->compressor->on('end', $this->expectCallableOnce());

        $data = str_repeat('hello', 100);
        foreach (str_split($data, 1) as $byte) {
            $this->compressor->write($byte);
        }
        $this->compressor->end();

        $this->assertEquals($data, gzuncompress($buffered));
    }
}
