<?php

use Clue\React\Zlib\Decompressor;

class DecompressorTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testCtorThrowsForInvalidEncoding()
    {
        new Decompressor(0);
    }
}
