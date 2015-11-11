# clue/zlib-react [![Build Status](https://travis-ci.org/clue/php-zlib-react.svg?branch=master)](https://travis-ci.org/clue/php-zlib-react)

Streaming zlib compressor and decompressor, built on top of [React PHP](http://reactphp.org/),
supporting compression and decompression of the following formats:

* [RFC 1952](https://tools.ietf.org/html/rfc1952) (GZIP compressed format)
* [RFC 1951](https://tools.ietf.org/html/rfc1951) (raw DEFLATE compressed format)
* [RFC 1950](https://tools.ietf.org/html/rfc1950) (ZLIB compressed format)

> Note: This project is in early alpha stage! Feel free to report any issues you encounter.

## Quickstart example

Once [installed](#install), you can use the following code to pipe a readable
gzip file stream into an decompressor which emits decompressed data events for
each individual file chunk:

```php
$loop = React\EventLoop\Factory::create();
$stream = new Stream(fopen('access.log.gz', 'r'), $loop);

$decompressor = ZlibFilterStream::createGzipDecompressor();

$decompressor->on('data', function ($data) {
    echo $data;
});

$stream->pipe($decompressor);

$loop->run();
```

See also the [examples](examples).

## Install

The recommended way to install this library is [through composer](https://getcomposer.org).
[New to composer?](https://getcomposer.org/doc/00-intro.md)

```bash
$ composer require clue/zlib-react:dev-master
```

## License

MIT
