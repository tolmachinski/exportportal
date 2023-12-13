<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::getDateFormatIfNotEmpty
 */
class GetDateFormatIfNotEmptyTest extends TestCase
{
    /**
     * @dataProvider emptyDates
     *
     * @param mixed $date
     */
    public function testGetDateFormatIfNotEmptyWithEmptyDate($date): void
    {
        $this->assertSame(getDateFormatIfNotEmpty($date, null, 'Y-m-d'), '—');
    }

    public function testGetDateFormatIfNotEmptyWithEmptyFormat(): void
    {
        $this->assertSame(getDateFormatIfNotEmpty('2021-11-11', null, null, 'test'), 'test');
    }

    public function emptyDates(): array
    {
        return [
            [''],
            [' '],
            [0 => null],
            ['\n'],
        ];
    }

}
