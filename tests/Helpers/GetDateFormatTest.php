<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::getDateFormat
 */
class GetDateFormatTest extends TestCase
{
    #region getDateFormat

    /**
     * @dataProvider emptyDates
     *
     * @param mixed $date
     */
    public function testGetDateFormatEmptyDate($date): void
    {
        $this->assertNull(getDateFormat($date));
    }

    /**
     * @dataProvider stringsDates
     *
     * @param mixed $date
     */
    public function testGetDateFormatEmptyFormat($date): void
    {
        $this->assertNull(getDateFormat($date, null, 'Y-m-d'));
    }

    public function testGetDateFormatEmptyReturnFormat(): void
    {
        $this->assertNull(getDateFormat('2021-11-11', null, null));
    }

    public function testGetDateFormatRightInputFormat(): void
    {
        $this->assertSame(getDateFormat('2021-11-11 00:00:00', null, 'Y-m-d'), '2021-11-11');
    }

    /**
     *
     * @dataProvider realDateTime
     *
     * @param mixed $date
     */
    public function testGetDateFormatWithDateTime($date): void
    {
        $this->assertSame(getDateFormat($date, null, 'Y-m-d'), '2021-11-11');
    }

    public function stringsDates(): array
    {
        return [
            ['2021-11-11'],
            ['11-11-2021'],
            ['2021-11-11 00:00'],
        ];
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

    public function realDateTime(): array
    {
        return [
            [\DateTime::createFromFormat('Y-m-d', '2021-11-11')],
            [\DateTime::createFromFormat('Y-m-d H:i', '2021-11-11 00:00')],
        ];
    }

}
