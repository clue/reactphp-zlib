<?php

use Clue\React\Zlib\ZlibFilterStream;

class ZlibFilterZlibCompressorTest extends TestCase
{
    private $compressor;

    public function setUp()
    {
        if (defined('HHVM_VERSION')) $this->markTestSkipped('Not supported on HHVM (ignores window size / encoding format)');

        $this->compressor = ZlibFilterStream::createZlibCompressor();
    }

    public function testCompressEmpty()
    {
        if (PHP_VERSION >= 7) $this->markTestSkipped('Not supported on PHP 7 (empty chunk will not be emitted)');

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
