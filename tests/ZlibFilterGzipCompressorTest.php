<?php

use Clue\React\Zlib\ZlibFilterStream;

class ZlibFilterGzipCompressorTest extends TestCase
{
    private $compressor;

    public function setUp()
    {
        $this->compressor = ZlibFilterStream::createGzipCompressor();
    }

    public function testCompressEmpty()
    {
        $os = DIRECTORY_SEPARATOR === '\\' ? "\x0a" : "\x03"; // NTFS(0x0a) or UNIX (0x03)
        $this->compressor->on('data', $this->expectCallableOnceWith("\x1f\x8b\x08\x00\x00\x00\x00\x00\x00" . $os . "\x03\x00" . "\x00\x00\x00\x00\x00\x00\x00\x00"));
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

        // PHP < 5.4 does not support gzdecode(), so let's assert this the other way around…
        $this->assertEquals(gzencode('hello world'), $buffered);
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

        // PHP < 5.4 does not support gzdecode(), so let's assert this the other way around…
        $this->assertEquals(gzencode($data), $buffered);
    }
}
