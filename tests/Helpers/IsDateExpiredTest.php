<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::isDateExpired
 */
class IsDateExpiredTest extends TestCase
{
    /**
     * @dataProvider expiredAndEmptyDates
     *
     * @param mixed $date
     */
    public function testIsDateExpiredEmptyData($date): void
    {
        $this->assertTrue(isDateExpired($date));
    }

    /**
     * @dataProvider notExpiredDates
     *
     * @param mixed $date
     */
    public function testIsDateExpiredNotExpiredData($date): void
    {
        $this->assertFalse(isDateExpired($date));
    }


    public function notExpiredDates(): array
    {
        $date = new \DateTime('+1 day');

        return [
            [$date->format('Y-m-d')],
        ];
    }

    public function expiredAndEmptyDates(): array
    {
        return [
            ['2021-11-11'],
            ['11-11-2021'],
            ['2021-11-11'],
            [''],
        ];
    }

}
