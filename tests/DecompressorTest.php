<?php

namespace Clue\Tests\React\Zlib;

use Clue\React\Zlib\Decompressor;

class DecompressorTest extends TestCase
{
    public function testCtorThrowsForInvalidEncoding()
    {
        $this->expectException(PHP_VERSION_ID >= 80000 ? \ValueError::class : \InvalidArgumentException::class);
        new Decompressor(0);
    }
}
