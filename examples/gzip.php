<?php

require __DIR__ . '/../vendor/autoload.php';

if (!defined('ZLIB_ENCODING_GZIP')) {
    fwrite(STDERR, 'Requires PHP 5.4+ with ext-zlib enabled' . PHP_EOL);
    exit(1);
}

$loop = React\EventLoop\Factory::create();

$in = new React\Stream\ReadableResourceStream(STDIN, $loop);
$out = new React\Stream\WritableResourceStream(STDOUT, $loop);

$compressor = Clue\React\Zlib\ZlibFilterStream::createCompressor(ZLIB_ENCODING_GZIP);
$in->pipe($compressor)->pipe($out);

$loop->run();
