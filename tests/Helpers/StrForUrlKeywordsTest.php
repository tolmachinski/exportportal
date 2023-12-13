<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * @internal
 * @covers ::strForUrlKeywords
 */
class StrForUrlKeywordsTest extends TestCase
{
    /**
     * @dataProvider tagsData
     *
     * @param mixed $output
     */
    public function testStrForUrlKeywordsWithData(string $string, $output): void
    {
        $this->assertSame(strForUrlKeywords($string), $output);
    }

    /**
     * @dataProvider exceptionData
     * @expectedException \TypeError
     *
     * @param mixed $argument
     */
    public function testStrForUrlKeywordsForException($argument)
    {
        $this->expectException(TypeError::class);
        strForUrlKeywords($argument);
    }

    public function tagsData(): iterable
    {
        yield ['', ''];
        yield ['test', 'test'];
        yield ['hello world', 'hello+world'];
        yield ['hello    world', 'hello+world'];
        yield [' hello / world', 'hello+/+world'];
        yield [' hello % world', 'hello+%+world'];
        yield ["\x5A\x6F \x5A\x6F", 'Zo+Zo'];
    }

    public function exceptionData(): iterable
    {
        $stringableObject = new class() {
            public function __toString()
            {
                return '';
            }
        };

        yield [[]];
        yield [null];
        yield [true];
        yield [false];
        yield [$stringableObject];
    }
}
