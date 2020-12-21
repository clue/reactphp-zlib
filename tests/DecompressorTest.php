<?php

namespace Clue\Tests\React\Zlib;

use Clue\React\Zlib\Decompressor;

class DecompressorTest extends TestCase
{
    public function testCtorThrowsForInvalidEncoding()
    {
        $this->expectException('InvalidArgumentException');
        new Decompressor(0);
    }
}
