<?php

declare(strict_types=0);

namespace voku\tests;

/**
 * Class HhvmTest
 *
 * @internal
 */
final class HhvmTest extends \PHPUnit\Framework\TestCase
{
    public function test1()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        static::assertFalse(@\grapheme_extract('', 0));
    }

    public function test2()
    {
        // Negative offset are not allowed but native PHP silently casts them to zero

        if (\defined('HHVM_VERSION_ID') || \PHP_VERSION_ID < 50535 || (50600 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 50621) || (70000 <= \PHP_VERSION_ID && \PHP_VERSION_ID < 70006)) {
            static::assertSame(0, \grapheme_strpos('abc', 'a', -1));
        } else {
            $tmp = \grapheme_strpos('abc', 'a', -1);
            if ($tmp !== false && $tmp !== 0) { // polyfill will fail in some versions ...
                static::assertFalse($tmp);
            } else {
                static::assertTrue(true);
            }
        }
    }

    public function test3()
    {
        static::assertSame('ÉJÀ', \grapheme_stristr('DÉJÀ', 'é'));
    }

    public function test4()
    {
        if (\PHP_VERSION_ID >= 50400) {
            static::assertSame('1×234¡56', \number_format(1234.557, 2, '¡', '×'));
        }
    }

    public function test5()
    {
        static::assertSame('nàlizæti', \grapheme_substr('Iñtërnâtiônàlizætiøn', 10, -2));
    }

    public function test6()
    {
        static::assertNull(\grapheme_strlen("\xE9 invalid UTF-8"));
    }

    public function test7()
    {
        static::assertFalse(\Normalizer::normalize("\xE9 invalid UTF-8"));
    }

    public function test8()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        static::assertSame('', @(\substr('', 0) . ''));
    }
}
