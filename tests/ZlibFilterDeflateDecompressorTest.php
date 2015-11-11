<?php

use Clue\React\Zlib\ZlibFilterStream;

class ZlibFilterDeflateDecompressorTest extends TestCase
{
    private $decompressor;

    public function setUp()
    {
        $this->decompressor = ZlibFilterStream::createDeflateDecompressor();
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
        if (defined('HHVM_VERSION')) $this->markTestSkipped('Not supported on HHVM (final chunk will not be emitted)');

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

    public function testInflateInvalid()
    {
        if (!defined('HHVM_VERSION')) $this->markTestSkipped('Only supported on HHVM (other engines do not reject invalid data)');

        $this->decompressor->on('data', $this->expectCallableNever());
        $this->decompressor->on('error', $this->expectCallableOnce());

        $this->decompressor->end('invalid');
    }
}
