<?php

use Clue\React\Zlib\Compressor;

class DeflateCompressorTest extends TestCase
{
    private $compressor;

    public function setUp()
    {
        $this->compressor = new Compressor(ZLIB_ENCODING_RAW);
    }

    public function testDeflateEmpty()
    {
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
