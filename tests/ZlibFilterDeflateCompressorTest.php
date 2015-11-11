<?php

use Clue\React\Zlib\ZlibFilterStream;

class ZlibFilterDeflateCompressorTest extends TestCase
{
    private $compressor;

    public function setUp()
    {
        $this->compressor = ZlibFilterStream::createDeflateCompressor();
    }

    public function testDeflateEmpty()
    {
        if (PHP_VERSION >= 7) $this->markTestSkipped('Not supported on PHP 7 (empty chunk will not be emitted)');

        $this->compressor->on('data', $this->expectCallableOnceWith("\x03\x00"));
        $this->compressor->on('end', $this->expectCallableOnce());

        $this->compressor->end();
    }

    public function testDeflateHelloWorld()
    {
        $this->compressor->on('data', function ($data) use (&$buffered) {
            $buffered .= $data;
        });
        $this->compressor->on('end', $this->expectCallableOnce());

        $this->compressor->end('hello world');

        $this->assertEquals('hello world', gzinflate($buffered));
    }

    public function testDeflateBig()
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

        $this->assertEquals($data, gzinflate($buffered));
    }
}
