<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * @internal
 * @covers ::urlToStr
 */
class UrlToStrTest extends TestCase
{
    /**
     * @dataProvider parameterData
     *
     * @param mixed $string
     * @param mixed $id
     * @param mixed $output
     */
    public function testUrlToStrWithData($string, $id, $output): void
    {
        $this->assertSame(urlToStr($string, $id), $output);
    }

    /**
     * @dataProvider exceptionDataForString
     * @expectedException \TypeError
     *
     * @param mixed $argument
     */
    public function testUrlToStrWithForFirstArguemntException($argument)
    {
        $this->expectException(TypeError::class);
        urlToStr($argument);
    }

    /**
     * @dataProvider exceptionDataForBool
     * @expectedException \TypeError
     *
     * @param mixed $argument
     */
    public function testUrlToStrWithForSecondArguemntException($argument)
    {
        $this->expectException(TypeError::class);
        urlToStr('hello', $argument);
    }

    public function parameterData(): iterable
    {
        yield ['', false, ''];
        yield ['test', false, 'Test'];
        yield ['hello-world', false, 'Hello world'];
        yield ['hello-world-123', false, 'Hello world 123'];
        yield ['hello-world-123', true, 'Hello world'];
        yield ['hello-world-123d', true, 'Hello world'];
        yield ['hello-world', true, 'Hello'];
        yield ['hello%world-hi', true, 'Hello%world'];
    }

    public function exceptionDataForString(): iterable
    {
        $stringableObject = new class() {
            public function __toString()
            {
                return '';
            }
        };

        yield [[]];
        yield [12345];
        yield [null];
        yield [true];
        yield [false];
        yield [$stringableObject];
    }

    public function exceptionDataForBool(): iterable
    {
        yield [[]];
        yield [12345];
        yield [null];
        yield ['hlkl'];
        yield [new class() {}];
    }
}

// End of file UrlToStrTest.php
// Location: /tests/Helpers/UrlToStrTest.php
