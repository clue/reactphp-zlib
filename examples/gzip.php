<?php

require __DIR__ . '/../vendor/autoload.php';

if (DIRECTORY_SEPARATOR === '\\') {
    fwrite(STDERR, 'Non-blocking console I/O not supported on Windows' . PHP_EOL);
    exit(1);
}

$in = new React\Stream\ReadableResourceStream(STDIN);
$out = new React\Stream\WritableResourceStream(STDOUT);

$compressor = new Clue\React\Zlib\Compressor(ZLIB_ENCODING_GZIP);
$in->pipe($compressor)->pipe($out);
