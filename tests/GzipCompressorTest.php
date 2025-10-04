<?php

namespace Clue\Tests\React\Zlib;

use Clue\React\Zlib\Compressor;

class GzipCompressorTest extends TestCase
{
    private $compressor;

    /** @var string */
    private $os;

    /**
     * @before
     */
    public function setUpCompressor()
    {
        $this->compressor = new Compressor(ZLIB_ENCODING_GZIP);
        $this->os = DIRECTORY_SEPARATOR !== '\\' ? "\x03" : (PHP_VERSION_ID >= 70200 ? "\x0a" : "\x0b"); // UNIX (0x03) or incorrect TOPS-20(0x0a) or NTFS(0x0b)
    }

    public function testCompressEmpty()
    {
        $this->compressor->on('data', $this->expectCallableOnceWith("\x1f\x8b\x08\x00\x00\x00\x00\x00\x00" . $this->os . "\x03\x00" . "\x00\x00\x00\x00\x00\x00\x00\x00"));
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

    public function testWriteWillOnlyFlushHeaderByDefaultToBufferDataBeforeFlushing()
    {
        $compressor = new Compressor(ZLIB_ENCODING_GZIP);

        $compressor->on('data', $this->expectCallableOnceWith("\x1f\x8b\x08\x00\x00\x00\x00\x00\x00" . $this->os));

        $compressor->write('hello');
    }

    public function testWriteWithSyncFlushWillFlushHeaderWithFirstChunkImmediately()
    {
        $compressor = new Compressor(ZLIB_ENCODING_GZIP, -1, ZLIB_SYNC_FLUSH);

        $compressor->on('data', $this->expectCallableOnceWith("\x1f\x8b\x08\x00\x00\x00\x00\x00\x00" . $this->os . "\xca\x48\xcd\xc9\xc9\x07\x00\x00\x00\xff\xff"));

        $compressor->write('hello');
    }

    public function testWriteWithFinishFlushWillFlushEntireGzipHeaderAndFooterWithFirstChunkImmediately()
    {
        $compressor = new Compressor(ZLIB_ENCODING_GZIP, -1, ZLIB_FINISH);

        $compressor->on('data', $this->expectCallableOnceWith("\x1f\x8b\x08\x00\x00\x00\x00\x00\x00" . $this->os . "\xcb\x48\xcd\xc9\xc9\x07\x00\x86\xa6\x10\x36" . "\x05\x00\x00\x00"));

        $compressor->write('hello');
    }

    public function testWriteAfterFinishFlushWillFlushEntireGzipWithSyncFlushWillFlushEntireGzipHeaderAndFooterAgainImmediately()
    {
        $compressor = new Compressor(ZLIB_ENCODING_GZIP, -1, ZLIB_FINISH);
        $compressor->write('hello');

        $compressor->on('data', $this->expectCallableOnceWith("\x1f\x8b\x08\x00\x00\x00\x00\x00\x00" . $this->os . "\xcb\x48\xcd\xc9\xc9\x07\x00\x86\xa6\x10\x36" . "\x05\x00\x00\x00"));

        $compressor->write('hello');
    }
}
