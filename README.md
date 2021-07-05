# clue/reactphp-zlib

[![CI status](https://github.com/clue/reactphp-zlib/workflows/CI/badge.svg)](https://github.com/clue/reactphp-zlib/actions)
[![installs on Packagist](https://img.shields.io/packagist/dt/clue/zlib-react?color=blue&label=installs%20on%20Packagist)](https://packagist.org/packages/clue/zlib-react)

Streaming zlib compressor and decompressor for [ReactPHP](https://reactphp.org/),
supporting compression and decompression of GZIP, ZLIB and raw DEFLATE formats.

**Table of contents**

* [Support us](#support-us)
* [Quickstart example](#quickstart-example)
* [Formats](#formats)
    * [GZIP format](#gzip-format)
    * [Raw DEFLATE format](#raw-deflate-format)
    * [ZLIB format](#zlib-format)
* [Usage](#usage)
    * [Compressor](#compressor)
    * [Decompressor](#decompressor)
* [Install](#install)
* [Tests](#tests)
* [License](#license)
* [More](#more)

## Support us

We invest a lot of time developing, maintaining and updating our awesome
open-source projects. You can help us sustain this high-quality of our work by
[becoming a sponsor on GitHub](https://github.com/sponsors/clue). Sponsors get
numerous benefits in return, see our [sponsoring page](https://github.com/sponsors/clue)
for details.

Let's take these projects to the next level together! 🚀

## Quickstart example

Once [installed](#install), you can use the following code to pipe a readable
gzip file stream into an decompressor which emits decompressed data events for
each individual log file chunk:

```php
$loop = React\EventLoop\Factory::create();
$stream = new React\Stream\ReadableResourceStream(fopen('access.log.gz', 'r'), $loop);

$decompressor = new Clue\React\Zlib\Decompressor(ZLIB_ENCODING_GZIP);
$stream->pipe($decompressor);

$decompressor->on('data', function ($data) {
    echo $data; // chunk of decompressed log data
});

$loop->run();
```

See also the [examples](examples).

## Formats

This library is a lightweight wrapper around the underlying zlib library.
The zlib library offers a number of different formats (sometimes referred to as *encodings*) detailled below.

### GZIP format

This library supports the GZIP compression format as defined in [RFC 1952](https://tools.ietf.org/html/rfc1952).
This is one of the more common compression formats and is used in several places:

* PHP: `ZLIB_ENCODING_GZIP` (PHP 5.4+ only)
* PHP: `gzdecode()` (PHP 5.4+ only) and `gzencode()`
* Files with `.gz` file extension, e.g. `.tar.gz` or `.tgz` archives (also known as "tarballs")
* `gzip` and `gunzip` (and family) command line tools
* [HTTP compression](https://en.wikipedia.org/wiki/HTTP_compression) with `Content-Encoding: gzip` header
* Java: `GZIPOutputStream`

Technically, this format uses [raw DEFLATE compression](#raw-deflate-format) wrapped in a GZIP header and footer:

```
10 bytes header (+ optional headers) + raw DEFLATE body + 8 bytes footer
```

### Raw DEFLATE format

This library supports the raw DEFLATE compression format as defined in [RFC 1951](https://tools.ietf.org/html/rfc1951).
The DEFLATE compression algorithm returns what we refer to as "raw DEFLATE format".
This raw DEFLATE format is commonly wrapped in container formats instead of being used directly:

* PHP: `ZLIB_ENCODING_RAW` (PHP 5.4+ only)
* PHP: `gzdeflate()` and `gzinflate()`
* Wrapped in [GZIP format](#gzip-format)
* Wrapped in [ZLIB format](#zlib-format)

> Note: This format is not to be confused with what some people call "deflate format" or "deflate encoding".
These names are commonly used to refer to what we call [ZLIB format](#zlib-format).

### ZLIB format

This library supports the ZLIB compression format as defined in [RFC 1950](https://tools.ietf.org/html/rfc1950).
This format is commonly used in a streaming context:

* PHP: `ZLIB_ENCODING_DEFLATE` (PHP 5.4+ only)
* PHP: `gzcompress()` and `gzuncompress()`
* [HTTP compression](https://en.wikipedia.org/wiki/HTTP_compression) with `Content-Encoding: deflate` header
* Java: `DeflaterOutputStream`
* Qt's [`qCompress()`](https://doc.qt.io/archives/qt-4.8/qbytearray.html#qCompress)
  and [`qUncompress()`](https://doc.qt.io/archives/qt-4.8/qbytearray.html#qUncompress)
  uses the ZLIB format prefixed with the uncompressed length (as `UINT32BE`).

Technically, this format uses [raw DEFLATE compression](#raw-deflate-format) wrapped in a ZLIB header and footer:

```
2 bytes header (+ optional headers) + raw DEFLATE body + 4 bytes footer
```

> Note: This format is often referred to as the "deflate format" or "deflate encoding".
This documentation avoids this name in order to avoid confusion with the [raw DEFLATE format](#raw-deflate-format).

## Usage

All classes use the `Clue\React\Zlib` namespace.

### Compressor

The `Compressor` class can be used to compress a stream of data.

It implements the [`DuplexStreamInterface`](https://github.com/reactphp/stream#duplexstreaminterface)
and accepts uncompressed data on its writable side and emits compressed data
on its readable side.

```php
$encoding = ZLIB_ENCODING_GZIP; // or ZLIB_ENCODING_RAW or ZLIB_ENCODING_DEFLATE
$compressor = new Clue\React\Zlib\Compressor($encoding);

$compressor->on('data', function ($data) {
    echo $data; // compressed binary data chunk
});

$compressor->write($uncompressed); // write uncompressed data chunk
```

This is particularly useful in a piping context:

```php
$input->pipe($filterBadWords)->pipe($compressor)->pipe($output);
```

For more details, see ReactPHP's
[`DuplexStreamInterface`](https://github.com/reactphp/stream#duplexstreaminterface).

### Decompressor

The `Decompressor` class can be used to decompress a stream of data.

It implements the [`DuplexStreamInterface`](https://github.com/reactphp/stream#duplexstreaminterface)
and accepts compressed data on its writable side and emits decompressed data
on its readable side.

```php
$encoding = ZLIB_ENCODING_GZIP; // or ZLIB_ENCODING_RAW or ZLIB_ENCODING_DEFLATE
$decompressor = new Clue\React\Zlib\Decompressor($encoding);

$decompressor->on('data', function ($data) {
    echo $data; // decompressed data chunk
});

$decompressor->write($compressed); // write compressed binary data chunk
```

This is particularly useful in a piping context:

```php
$input->pipe($decompressor)->pipe($filterBadWords)->pipe($output);
```

For more details, see ReactPHP's
[`DuplexStreamInterface`](https://github.com/reactphp/stream#duplexstreaminterface).

## Install

The recommended way to install this library is [through Composer](https://getcomposer.org/).
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

This project follows [SemVer](https://semver.org/).
This will install the latest supported version:

```bash
$ composer require clue/zlib-react:^1.1
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

This project aims to run on any platform and thus does not require any PHP
extensions besides `ext-zlib` and supports running on PHP 7 through current PHP 8+.

The `ext-zlib` extension is required for handling the underlying data compression
and decompression.
This extension is already installed as part of many PHP distributions out-of-the-box,
e.g. it ships with Debian/Ubuntu-based PHP installations and Windows-based
builds by default. If you're building PHP from source, you may have to
[manually enable](https://www.php.net/manual/en/zlib.installation.php) it.

We're committed to providing a smooth upgrade path for legacy setups.
If you need to support legacy PHP versions and legacy HHVM, you may want to
check out the legacy `v0.2.x` release branch.
This legacy release branch also provides an installation candidate that does not
require `ext-zlib` during installation but uses runtime checks instead.
In this case, you can install this project like this:

```bash
$ composer require "clue/zlib-react:^1.0||^0.2.2"
```

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](https://getcomposer.org/):

```bash
$ composer install
```

To run the test suite, go to the project root and run:

```bash
$ php vendor/bin/phpunit
```

## License

This project is released under the permissive [MIT license](LICENSE).

> Did you know that I offer custom development services and issuing invoices for
  sponsorships of releases and for contributions? Contact me (@clue) for details.

## More

* If you want to learn more about processing streams of data, refer to the documentation of
  the underlying [react/stream](https://github.com/reactphp/stream) component.

* If you want to process compressed tarballs (`.tar.gz` and `.tgz` file extension), you may
  want to use [clue/reactphp-tar](https://github.com/clue/reactphp-tar) on the decompressed stream.
