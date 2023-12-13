<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::strForUrl
 */
class StrForUrlTest extends TestCase
{
    /**
     * @dataProvider oneParameterOnly
     *
     * @param mixed $string
     * @param mixed $output
     */
    public function testStrForUrlWithData($string, $output): void
    {
        $this->assertSame(strForURL($string), $output);
    }

    /**
     * @dataProvider allParameters
     *
     * @param mixed $string
     * @param mixed $delimiter
     * @param mixed $lower
     * @param mixed $output
     */
    public function testStrForUrlWithAllParametersData($string, $delimiter, $lower, $output): void
    {
        $this->assertSame(strForURL($string, $delimiter, $lower), $output);
    }

    public function oneParameterOnly(): iterable
    {
        yield ['', ''];
        yield ['test', 'test'];
        yield ['hello. world', 'hello-world'];
        yield ["\x5A\x6F \x5A\x6F", 'zo-zo'];
        yield [" test 1.\2 3 hi", 'test-1-3-hi'];
        yield ['test 1.//hi', 'test-1hi'];
        yield ['Test_hi', 'testhi'];
        yield ['я люблю', 'a-lublu'];
        yield ['Übérmensch', 'ubermensch'];
        yield ['<script>', 'script'];
    }

    public function allParameters(): iterable
    {
        yield ['', '', '', ''];
        yield [' hello Hi', '+', true, 'hello+hi'];
        yield [' hello Hi', '.', false, 'hello.Hi'];
        yield [' Hello Hi 123%', 'я', false, 'HelloяHiя123'];
    }
}

// End of file StrForUrlTest.php
// Location: /tests/Helpers/StrForUrlTest.php
