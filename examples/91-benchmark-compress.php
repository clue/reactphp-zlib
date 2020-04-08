<?php

// This benchmarking example reads a stream of dummy data and displays how fast
// it can be compressed.
//
// You can run the benchmark like this:
//
// $ php examples/91-benchmark-compress.php
//
// This runs the equivalent of:
//
// $ dd if=/dev/zero bs=1M count=1k status=progress | php examples/gzip.php > /dev/null
//
// Expect this to be only slightly slower than the equivalent:
//
// $ dd if=/dev/zero bs=1M count=1k status=progress | gzip > /dev/null

require __DIR__ . '/../vendor/autoload.php';

if (DIRECTORY_SEPARATOR === '\\') {
    fwrite(STDERR, 'Non-blocking console I/O not supported on Windows' . PHP_EOL);
    exit(1);
}

if (!defined('ZLIB_ENCODING_GZIP')) {
    fwrite(STDERR, 'Requires PHP 5.4+ with ext-zlib enabled' . PHP_EOL);
    exit(1);
}


if (extension_loaded('xdebug')) {
    echo 'NOTICE: The "xdebug" extension is loaded, this has a major impact on performance.' . PHP_EOL;
}

$loop = React\EventLoop\Factory::create();

// read 1 MiB * 1 Ki times
$count = 0;
$stream = new React\Stream\ReadableResourceStream(fopen('/dev/zero', 'r'), $loop, 1024*1024);
$stream->on('data', function () use (&$count, $stream) {
    if (++$count > 1024) {
        $stream->close();
    }
});

$compressor = new Clue\React\Zlib\Compressor(ZLIB_ENCODING_GZIP);
$stream->pipe($compressor);

// count number of input bytes before compression
$bytes = 0;
$stream->on('data', function ($chunk) use (&$bytes) {
    $bytes += strlen($chunk);
});

// report progress periodically
$timer = $loop->addPeriodicTimer(0.05, function () use (&$bytes) {
    echo "\rCompressed $bytes bytes…";
});

// report results once the stream closes
$start = microtime(true);
$stream->on('close', function () use (&$bytes, $start, $loop, $timer) {
    $time = microtime(true) - $start;
    $loop->cancelTimer($timer);

    echo "\rCompressed $bytes bytes in " . round($time, 1) . 's => ' . round($bytes / $time / 1000000, 1) . ' MB/s' . PHP_EOL;
});

$loop->run();
