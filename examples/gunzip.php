<?php

require __DIR__ . '/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$in = new React\Stream\Stream(STDIN, $loop);
$out = new React\Stream\Stream(STDOUT, $loop);

$decompressor = Clue\React\Zlib\ZlibFilterStream::createGzipDecompressor();
$in->pipe($decompressor)->pipe($out);

$decompressor->on('error', function ($e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
});

$loop->run();
