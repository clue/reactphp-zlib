<?php

require __DIR__ . '/../vendor/autoload.php';

if (DIRECTORY_SEPARATOR === '\\') {
    fwrite(STDERR, 'Non-blocking console I/O not supported on Windows' . PHP_EOL);
    exit(1);
}

$in = new React\Stream\ReadableResourceStream(STDIN);
$out = new React\Stream\WritableResourceStream(STDOUT);

$decompressor = new Clue\React\Zlib\Decompressor(ZLIB_ENCODING_GZIP);
$in->pipe($decompressor)->pipe($out);

$decompressor->on('error', function ($e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
});
