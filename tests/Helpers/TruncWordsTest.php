<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers truncWords
 */
class TruncWordsTest extends TestCase
{
    /**
     * @dataProvider correctlyData
     * Test for the correct execution of the helper
     */
    public function testTruncWordsCorrectly(string $string, string $rightData, int $words): void
    {
        $this->assertSame($rightData, truncWords($string, $words));
    }

    /**
     * @dataProvider wrongData
     * Test for the incorrect execution of the helper
     */
    public function testTruncWordsWrong(string $string, string $rightData, int $words): void
    {
        $this->assertNotSame($rightData, truncWords($string, $words));
    }

    /**
     * @dataProvider emptyStringData
     * Test for the incorrect execution of the helper with empty string
     */
    public function testTruncWordsEmptyString(string $string, string $rightData, int $words): void
    {
        $this->assertNotSame($rightData, truncWords($string, $words));
    }

    /**
     * @dataProvider emptyWordsData
     * Test for the incorrect execution of the helper with empty words
     */
    public function testTruncWordsEmptyWords(string $string, string $rightData, int $words): void
    {
        $this->assertNotSame($rightData, truncWords($string, $words));
    }

    /**
     * Strings for correctly testing.
     */
    public function correctlyData(): iterable
    {
        yield ['Quisque dapibus, velit quis vulputate aliquam', 'Quisque ...', 1];
        yield ['Class aptent taciti sociosqu ad litora', 'Class aptent ...', 2];
        yield ['Hello world! This is PHP', 'Hello world! This ...', 3];
        yield ['Maecenas id metus consectetur, condimentum erat', 'Maecenas id metus consectetur, ...', 4];
        yield ['Integer nisi ipsum, ultrices at eros', 'Integer nisi ipsum, ultrices at eros', 5];
        yield ['', '', 5];
        yield ['Привет мир! Это я PHP', 'Привет мир! Это ...', 3];
        yield ['Salut Lume! Sunt eu php', 'Salut Lume! Sunt ...', 3];
    }

    /**
     * Strings for correctly testing.
     */
    public function wrongData(): iterable
    {
        yield ['Quisque dapibus, velit quis vulputate aliquam', 'Quisque dapibus, velit ...', 1];
        yield ['Class aptent taciti sociosqu ad litora', 'Class ...', 2];
        yield ['Hello world! This is PHP', 'Hello world! This This is PHP ...', 3];
        yield ['Maecenas id metus consectetur', 'Maecenas id metus consectetur, ...', 4];
        yield ['Integer nisi ipsum, ultrices at eros', 'Integer nisi ipsum, ultrices at', 5];
        yield ['', '...', 5];
        yield ['Привет мир! Это я PHP', 'Hello world! This ...', 3];
        yield ['Salut Lume! Sunt eu php', 'Salut Lume! Acesta eu ...', 3];
    }

    /**
     * Strings for wrong testing empty string.
     */
    public function emptyStringData(): iterable
    {
        yield ['', 'Quisque ...', 1];
        yield ['', 'Class aptent ...', 2];
        yield ['', 'Hello world! This ...', 3];
        yield ['', 'Maecenas id metus consectetur, ...', 4];
        yield ['', 'Integer nisi ipsum, ultrices at eros', 5];
    }

    /**
     * Strings for correctly testing.
     */
    public function emptyWordsData(): iterable
    {
        yield ['Quisque dapibus, velit quis vulputate aliquam', 'Quisque dapibus, velit ...', 0];
        yield ['Class aptent taciti sociosqu ad litora', 'Class ...', 0];
        yield ['Hello world! This is PHP', 'Hello world! This This is PHP ...', 0];
        yield ['Maecenas id metus consectetur', 'Maecenas id metus consectetur, ...', 0];
        yield ['Integer nisi ipsum, ultrices at eros', 'Integer nisi ipsum, ultrices at', 0];
    }
}
