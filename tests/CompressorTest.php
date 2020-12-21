<?php

namespace Clue\Tests\React\Zlib;

use Clue\React\Zlib\Compressor;

class CompressorTest extends TestCase
{
    public function testCtorThrowsForInvalidEncoding()
    {
        $this->expectException('InvalidArgumentException');
        new Compressor(0);
    }
}
