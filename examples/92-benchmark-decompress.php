<?php

// This benchmarking example reads a compressed file and displays how fast
// it can be decompressed.
//
// Before starting the benchmark, you have to create a (dummy) compressed file first, such as:
//
// $ dd if=/dev/zero bs=1M count=1k status=progress | gzip > null.gz
//
// You can run the benchmark like this:
//
// $ php examples/92-benchmark-decompress.php null.gz
//
// Expect this to be slightly faster than the (totally unfair) equivalent:
//
// $ gunzip < null.gz | dd of=/dev/null status=progress
//
// Expect this to be somewhat faster than:
//
// $ php examples/gunzip.php < null.gz | dd of=/dev/zero status=progress

use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

if (DIRECTORY_SEPARATOR === '\\') {
    fwrite(STDERR, 'Non-blocking console I/O not supported on Windows' . PHP_EOL);
    exit(1);
}

if ($argc !== 2) {
    fwrite(STDERR, 'No archive given, requires single argument' . PHP_EOL);
    exit(1);
}

if (extension_loaded('xdebug')) {
    echo 'NOTICE: The "xdebug" extension is loaded, this has a major impact on performance.' . PHP_EOL;
}

$in = new React\Stream\ReadableResourceStream(fopen($argv[1], 'r'));
$stream = new Clue\React\Zlib\Decompressor(ZLIB_ENCODING_GZIP);
$in->pipe($stream);

$bytes = 0;
$stream->on('data', function ($chunk) use (&$bytes) {
    $bytes += strlen($chunk);
});

$stream->on('error', 'printf');

//report progress periodically
$timer = Loop::addPeriodicTimer(0.2, function () use (&$bytes) {
    echo "\rDecompressed $bytes bytes…";
});

// show stats when stream ends
$start = microtime(true);
$stream->on('close', function () use (&$bytes, $start, $timer) {
    $time = microtime(true) - $start;
    Loop::cancelTimer($timer);

    echo "\rDecompressed $bytes bytes in " . round($time, 1) . 's => ' . round($bytes / $time / 1000000, 1) . ' MB/s' . PHP_EOL;
});
