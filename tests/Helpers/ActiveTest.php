<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @covers ::active
 */
class ActiveTest extends TestCase
{
    /**
     * @dataProvider stringsAllTypesDataProvider
     *
     * @param mixed $value
     * @param mixed $compare
     * @param mixed $output
     */
    public function testActiveTrue($value, $compare, $output): void
    {
        $this->assertSame(active($value, $compare), $output);
    }

    /**
     * @dataProvider stringsWithNotEqualValuesDataProvider
     *
     * @param mixed $value
     * @param mixed $compare
     * @param mixed $output
     */
    public function testActiveFalse($value, $compare, $output): void
    {
        $this->assertSame(active($value, $compare), $output);
    }

    /**
     * negataive test of input.
     */
    public function testFailWhenInputCannotBeCastedToString(): void
    {
        $input = new stdClass();

        $this->expectErrorMessage('Object of class stdClass could not be converted to string');
        cleanOutput($input);
    }

    public function stringsAllTypesDataProvider(): array
    {
        $init = curl_init('http://www.example.com/');
        // creating of stringable object
        $stringableObject = new class() {
            public function __toString()
            {
                return '';
            }
        };

        return [
            ['example', 'example', 'active'],
            [1.245, 1.245, 'active'],
            [1.245, '1.245', 'active'],
            [1, 1, 'active'],
            [1, '1', 'active'],
            ["\x5A\x6F", 'Zo', 'active'],
            ["\x5A\x6F", "\x5A\x6F", 'active'],
            [['123'], ['123'], 'active'],
            [['123'], [123], 'active'],
            [$init, $init, 'active'],
            [$stringableObject, $stringableObject, 'active'],
            [$stringableObject, '', 'active'],
            [true, true, 'active'],
            [true, 'true', 'active'],
            [null, null, 'active'],
        ];
    }

    public function stringsWithNotEqualValuesDataProvider(): array
    {
        return [
            ['example', '  example', ''],
            ['example', 'example ', ''],
            ['example', '%example', ''],
        ];
    }
}
