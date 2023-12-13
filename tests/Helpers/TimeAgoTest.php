<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \timeAgo
 */
class TimeAgoTest extends TestCase
{
    /**
     * @dataProvider correctStringData
     * Test for the correct execution of the helper
     */
    public function testTimeAgoCorrectlyText(string $date, string $format, bool $limit, bool $ago, string $expected): void
    {
        $this->assertSame($expected, timeAgo($this->prepareDate($date), $format, $limit, $ago));
    }

    /**
     * @dataProvider wrongStringData
     * Test for the wrong execution of the helper
     */
    public function testTimeAgoWrongText(string $date, string $format, bool $limit, bool $ago, string $expected): void
    {
        $this->assertNotSame($expected, timeAgo($this->prepareDate($date), $format, $limit, $ago));
    }

    /**
     * @dataProvider customDateFormats
     * Test for the wrong execution of the helper
     */
    public function testTimeAgoCustomDates(string $date, string $format, bool $limit, bool $ago, string $expected): void
    {
        $this->assertSame($expected, timeAgo($date, $format, $limit, $ago));
    }

    /**
     * Data provider for Correctly Text results.
     */
    public function correctStringData(): iterable
    {
        yield ['-1 day', 'Y,m,d', false, true, '1 day ago'];
        yield ['-1 week', 'Y,m,d', false, false, '7 days'];
        yield ['-1 month -2 days', 'Y,m,d', false, true, '1 month, 2 days ago'];
        yield ['-1 year', 'Y,m', false, true, '1 year ago'];
        yield ['+1 year', 'Y,m', false, true, '0 seconds'];
    }

    /**
     * Data provider for Wrong Text results.
     */
    public function wrongStringData(): iterable
    {
        yield ['-1 day', 'Y,m,d', true, true, '1 day'];
        yield ['-1 week', 'Y,m,d,H', false, false, '6 days'];
        yield ['-1 month -2 days', 'Y,m,d', false, true, '1 month, 3 days ago'];
        yield ['-1 year', 'Y,m', false, true, '1 years ago'];
        yield ['+1 year', 'Y,m', false, true, '1 years'];
    }

    /**
     * Data provider with custom date formats.
     */
    public function customDateFormats()
    {
        $currentDate = new \DateTime();
        $unixEpochDate = (new \DateTime())->setTimestamp(0);
        $dateDiff = $currentDate->diff($unixEpochDate);

        yield [date('Y-m-d', time()), 'Y,m,d',  false, true, 'recently'];
        yield ['2022.02.02', 'Y',  false, false, sprintf('%d years', $dateDiff->y)];
        yield ['2022.02.02', 'Y,m',  false, false, sprintf('%d years, %d months', $dateDiff->y, $dateDiff->m)];
        yield ['2022.02.02', 'Y,m,d',  false, false, sprintf('%d years, %d months, %d days', $dateDiff->y, $dateDiff->m, $dateDiff->d)];
        yield ['today', 'Y,m,d',  false, true, 'recently'];
    }

    /**
     * Prepare date.
     */
    private function prepareDate(string $date): string
    {
        return date('Y-m-d h:i:s', strtotime(date('Y-m-d h:i:s') . $date));
    }
}
