<?php

namespace Clue\Tests\React\Zlib;

use Clue\React\Zlib\Compressor;

class CompressorTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testCtorThrowsForInvalidEncoding()
    {
        new Compressor(0);
    }
}
