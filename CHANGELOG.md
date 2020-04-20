# Changelog

## 0.2.2 (2020-04-20)

*   Feature: Add dedicated `Compressor` and `Decompressor` classes, deprecate `ZlibFilterStream`.
    (#21 by @clue)

    ```php
    // old
    $compressor = Clue\React\Zlib\ZlibFilterStream::createGzipCompressor();

    // new
    $compressor = new Clue\React\Zlib\Compressor(ZLIB_ENCODING_GZIP);
    ```

*   Feature / Bug: Work around compressing empty stream on PHP 7+.
    (#22 by @clue)

*   Add compression and decompression benchmarks.
    (#24 by @clue)

*   Add support / sponsorship info.
    (#20 by @clue)

*   Improve test suite by running tests on PHP 7.4 and simplify test matrix
    and run tests on Windows.
    (#19 and #23 by @clue)

## 0.2.1 (2018-05-11)

*   Feature / Fix: Add backpressure support and support `pause()`/`resume()`.
    (#18 by @clue)

*   Update project homepage.
    (#17 by @clue)

## 0.2.0 (2017-08-19)

* Feature / BC break: Update to Stream v0.6 API and forward compatibility with Stream v1.0
  (#13 and #15 by @clue)

* Fix: Remove event listeners once closed
  (#14 by @clue)

* Improve documentation
  (#15 and #16 by @clue)

* Improve test suite by adding PHPUnit to require-dev and
  Lock Travis distro so new future defaults will not break the build
  (#11 and #12 by @clue)

## 0.1.0 (2015-11-12)

* First tagged release
