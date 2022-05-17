<?php

namespace Clue\Tests\React\Zlib;

use Clue\React\Zlib\Compressor;

class CompressorTest extends TestCase
{
    public function testCtorThrowsForInvalidEncoding()
    {
        $this->expectException(PHP_VERSION_ID >= 80000 ? \ValueError::class : \InvalidArgumentException::class);
        new Compressor(0);
    }

    public function testCtorThrowsForInvalidEncodingAndUnsetsUsedErrorHandler()
    {
        $handler = set_error_handler(function(){});

        restore_error_handler();

        try {
            new Compressor(0);
        } catch (\ValueError $e) {
            // handle Error to unset Error handler afterwards (PHP >= 8.0)
        } catch (\InvalidArgumentException $e) {
            // handle Error to unset Error handler afterwards (PHP < 8.0)
        }
        $checkHandler = set_error_handler(function(){});
        restore_error_handler();

        $this->assertEquals($handler, $checkHandler);
    }
}
