<?php

use Clue\React\Zlib\ZlibFilterStream;

class ZlibFilterGzipCompressorTest extends TestCase
{
    private $compressor;

    public function setUp()
    {
        if (defined('HHVM_VERSION')) $this->markTestSkipped('Not supported on HHVM (ignores window size / encoding format)');

        $this->compressor = ZlibFilterStream::createGzipCompressor();
    }

    public function testCompressEmpty()
    {
        if (PHP_VERSION >= 7) $this->markTestSkipped('Not supported on PHP 7 (empty chunk will not be emitted)');

        $os = "\x03"; // UNIX (0x03) or UNKNOWN (0xFF)
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
