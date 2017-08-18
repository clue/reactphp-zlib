<?php

require __DIR__ . '/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$in = new React\Stream\ReadableResourceStream(STDIN, $loop);
$out = new React\Stream\WritableResourceStream(STDOUT, $loop);

$compressor = Clue\React\Zlib\ZlibFilterStream::createGzipCompressor(1);
$in->pipe($compressor)->pipe($out);

$loop->run();
