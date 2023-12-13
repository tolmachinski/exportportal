<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @covers ::checked
 */
class CheckedTest extends TestCase
{
    /**
     * @dataProvider stringsWithEqualValuesDataProvider
     *
     * @param mixed $value
     * @param mixed $compare
     * @param mixed $output
     */
    public function testCheckedTrue($value, $compare, $output): void
    {
        $this->assertSame(checked($value, $compare), $output);
    }

    /**
     * @dataProvider stringsWithNotEqualValuesDataProvider
     *
     * @param mixed $value
     * @param mixed $compare
     * @param mixed $output
     */
    public function testCheckedFalse($value, $compare, $output): void
    {
        $this->assertSame(checked($value, $compare), $output);
    }

    /**
     * @dataProvider allTypesDataProvider
     *
     * @param mixed $value
     * @param mixed $compare
     * @param mixed $output
     */
    public function testWhenInputCanBeCastedToString($value, $compare, $output): void
    {
        $this->assertSame(checked($value, $compare), $output);
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

    public function stringsWithEqualValuesDataProvider(): array
    {
        return [
            ['example', 'example', 'checked'],
            ['example', ['    example', 'example'], 'checked'],
        ];
    }

    public function stringsWithNotEqualValuesDataProvider(): array
    {
        return [
            ['example', ['   example', 'example     '], null],
            ['example', ['example  ', 'example     '], null],
            ['example', ['%example', 'example     '], null],
        ];
    }

    public function allTypesDataProvider(): array
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
            [1.245, 1.245, 'checked'],
            [1.245, '1.245', 'checked'],
            [1, 1, 'checked'],
            [1, '1', 'checked'],
            ["\x5A\x6F", 'Zo', 'checked'],
            ["\x5A\x6F", "\x5A\x6F", 'checked'],
            [['123'], ['123'], 'checked'],
            [['123'], [123], 'checked'],
            [$init, $init, 'checked'],
            [$stringableObject, $stringableObject, 'checked'],
            [$stringableObject, '', 'checked'],
            [true, true, 'checked'],
            [true, 'true', 'checked'],
            [null, null, 'checked'],
        ];
    }
}
