<?php

class FunctionalExamplesTest extends TestCase
{
    public function setUp()
    {
        if (defined('HHVM_VERSION')) $this->markTestSkipped('Not supported on HHVM (ignores window size / encoding format)');
    }
    public function testChain()
    {
        $in = 'hello world';

        chdir(__DIR__ . '/../examples');
        $out = exec('echo ' . escapeshellarg($in) . ' | php gzip.php | php gunzip.php');

        $this->assertEquals('hello world', $out);
    }

    public function testEmpty()
    {
        $out = exec('cat ' . escapeshellarg(__DIR__ . '/fixtures/empty.gz') . ' | php ' . escapeshellarg(__DIR__ . '/../examples/gunzip.php'));

        $this->assertEquals('', $out);
    }

    public function testHelloWorld()
    {
        $out = exec('cat ' . escapeshellarg(__DIR__ . '/fixtures/helloworld.gz') . ' | php ' . escapeshellarg(__DIR__ . '/../examples/gunzip.php'));

        $this->assertEquals('hello world', $out);
    }
}
