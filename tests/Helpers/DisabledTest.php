<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::disabled
 */
class DisabledTest extends TestCase
{
    /**
     * @dataProvider stringsAllTypesDataProvider
     *
     * @param mixed $value
     * @param mixed $compare
     * @param mixed $output
     */
    public function testDisabledTrue($value, $compare, $output): void
    {
        $this->assertSame(disabled($value, $compare), $output);
    }

    /**
     * @dataProvider stringsWithNotEqualValuesDataProvider
     *
     * @param mixed $value
     * @param mixed $compare
     * @param mixed $output
     */
    public function testDisabledFalse($value, $compare, $output): void
    {
        $this->assertSame(disabled($value, $compare), $output);
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
            ['example', 'example', 'disabled = \'disabled\''],
            [1.245, 1.245, 'disabled = \'disabled\''],
            [1.245, '1.245', 'disabled = \'disabled\''],
            [1, 1, 'disabled = \'disabled\''],
            [1, '1', 'disabled = \'disabled\''],
            ["\x5A\x6F", 'Zo', 'disabled = \'disabled\''],
            ["\x5A\x6F", "\x5A\x6F", 'disabled = \'disabled\''],
            [['123'], ['123'], 'disabled = \'disabled\''],
            [['123'], [123], 'disabled = \'disabled\''],
            [$init, $init, 'disabled = \'disabled\''],
            [$stringableObject, $stringableObject, 'disabled = \'disabled\''],
            [$stringableObject, '', 'disabled = \'disabled\''],
            [true, true, 'disabled = \'disabled\''],
            [true, 'true', 'disabled = \'disabled\''],
            [null, null, 'disabled = \'disabled\''],
        ];
    }

    public function stringsWithNotEqualValuesDataProvider(): array
    {
        return [
            ['example', '  example', null],
            ['example', 'example ', null],
            ['example', '%example', null],
        ];
    }
}
