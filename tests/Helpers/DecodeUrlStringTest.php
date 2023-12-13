<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::decodeUrlString
 */
class DecodeUrlStringTest extends TestCase
{
    /**
    * @dataProvider areNotStringsDataProvider
    * negataive test of input is not string
    */
    public function testFailWhenInputCannotBeCastedToString($input): void
    {
        $this->expectError();
        decodeUrlString($input);
    }

    /**
     * @dataProvider stringsWithNotUtfDataProvider
     *
     * @param mixed $input
     */
    public function testDecodeUrlStringUtfEncode($input, $output): void
    {
        $this->assertSame(decodeUrlString($input), $output);
    }

    /**
    * @dataProvider stringsWithUrlPathDataProvider
    *
    * @param mixed $input
    */
    public function testDecodeUrlStringUrlDecode($input, $output): void
    {
        $this->assertSame(decodeUrlString($input), $output);
    }

    public function areNotStringsDataProvider(): array
    {
        $init = curl_init("http://www.example.com/");
        //creating of stringable object
        $stringableObject = new class {
            public function __toString()
            {
                return '';
            }
        };

        return [
            [1.245],
            [1],
            [['123']],
            [$init],
            [$stringableObject],
            [true],
            [null],
        ];

    }

    public function stringsWithNotUtfDataProvider(): array
    {
        return [
            ["\x5A\x6F", 'Zo'],
        ];
    }

    public function stringsWithUrlPathDataProvider(): array
    {
        return [
            ['name%20%26%20surname/green+and+red', 'name & surname/green and red'],
        ];
    }
}
