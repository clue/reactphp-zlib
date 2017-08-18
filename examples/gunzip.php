<?php

require __DIR__ . '/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$in = new React\Stream\ReadableResourceStream(STDIN, $loop);
$out = new React\Stream\WritableResourceStream(STDOUT, $loop);

$decompressor = Clue\React\Zlib\ZlibFilterStream::createGzipDecompressor();
$in->pipe($decompressor)->pipe($out);

$decompressor->on('error', function ($e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
});

$loop->run();
