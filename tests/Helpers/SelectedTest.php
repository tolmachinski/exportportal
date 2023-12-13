<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::selected
 */
class SelectedTest extends TestCase
{
    /**
     * @dataProvider stringsAllTypesDataProvider
     *
     * @param mixed $value
     * @param mixed $compare
     * @param mixed $output
     */
    public function testSelectedTrue($value, $compare, $output): void
    {
        $this->assertSame(selected($value, $compare), $output);
    }

    /**
     * @dataProvider stringsWithNotEqualValuesDataProvider
     *
     * @param mixed $value
     * @param mixed $compare
     * @param mixed $output
     */
    public function testSelectedFalse($value, $compare, $output): void
    {
        $this->assertSame(selected($value, $compare), $output);
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
            ['example', 'example', 'selected'],
            [1.245, 1.245, 'selected'],
            [1.245, '1.245', 'selected'],
            [1, 1, 'selected'],
            [1, '1', 'selected'],
            ["\x5A\x6F", 'Zo', 'selected'],
            ["\x5A\x6F", "\x5A\x6F", 'selected'],
            [['123'], ['123'], 'selected'],
            [['123'], [123], 'selected'],
            [$init, $init, 'selected'],
            [$stringableObject, $stringableObject, 'selected'],
            [$stringableObject, '', 'selected'],
            [true, true, 'selected'],
            [true, 'true', 'selected'],
            [null, null, 'selected'],
        ];
    }

    public function stringsWithNotEqualValuesDataProvider(): array
    {
        return [
            ['example', '  example', ''],
            ['example', 'example  ', ''],
            ['example', '%example', ''],
        ];
    }
}
