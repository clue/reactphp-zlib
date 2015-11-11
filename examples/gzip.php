<?php

require __DIR__ . '/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$in = new React\Stream\Stream(STDIN, $loop);
$out = new React\Stream\Stream(STDOUT, $loop);

$compressor = Clue\React\Zlib\ZlibFilterStream::createGzipCompressor(1);
$in->pipe($compressor)->pipe($out);

$loop->run();
