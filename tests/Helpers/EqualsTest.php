<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::equals
 */
class EqualsTest extends TestCase
{
    /**
     * @dataProvider stringsAllTypesDataProvider
     *
     * @param mixed $value
     * @param mixed $compare
     * @param mixed $output
     */
    public function testEqualsTrue($value, $compare, $output): void
    {
        $this->assertSame(equals($value, $compare, 'equal'), $output);
    }

    /**
     * @dataProvider stringsWithNotEqualValuesDataProvider
     *
     * @param mixed $value
     * @param mixed $compare
     * @param mixed $output
     */
    public function testEqualsFalse($value, $compare, $output): void
    {
        $this->assertSame(equals($value, $compare, 'equal'), $output);
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
            ['example', 'example', 'equal'],
            [1.245, 1.245, 'equal'],
            [1.245, '1.245', 'equal'],
            [1, 1, 'equal'],
            [1, '1', 'equal'],
            ["\x5A\x6F", 'Zo', 'equal'],
            ["\x5A\x6F", "\x5A\x6F", 'equal'],
            [['123'], ['123'], 'equal'],
            [['123'], [123], 'equal'],
            [$init, $init, 'equal'],
            [$stringableObject, $stringableObject, 'equal'],
            [$stringableObject, '', 'equal'],
            [true, true, 'equal'],
            [true, 'true', 'equal'],
            [null, null, 'equal'],
        ];
    }

    public function stringsWithNotEqualValuesDataProvider(): array
    {
        return [
            ['example', '  example', null],
            ['example', 'example  ', null],
            ['example', '%example', null],
        ];
    }
}
