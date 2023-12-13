<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::varToString
 */
class VarToStringTest extends TestCase
{
    public function testVarToStringFalse(): void
    {
        $this->assertSame(varToString(false), 'a boolean value (false)');
    }

    public function testVarToStringTrue(): void
    {
        $this->assertSame(varToString(true), 'a boolean value (true)');
    }

    public function testVarToStringNull(): void
    {
        $this->assertSame(varToString(null), 'null');
    }

    public function testVarToStringShortString(): void
    {
        $this->assertSame(varToString('hello'), 'a string ("hello")');
    }

    public function testVarToStringLongString(): void
    {
        $lorem256 = 'Nam quis nulla. Integer malesuada. In in enim a arcu imperdiet malesuada. Sed vel lectus. Donec odio urna, tempus molestie, porttitor ut, iaculis quis, sem. Phasellus rhoncus. Aenean id metus id velit ullamcorper pulvinar. Vestibulum fermentum tortor id mi';
        $lorem256Expected = 'a string ("Nam quis nulla. Integer malesuada. In in enim a arcu imperdiet malesuada. Sed vel lectus. Donec odio urna, tempus molestie, porttitor ut, iaculis quis, sem. Phasellus rhoncus. Aenean id metus id velit ullamcorper pulvinar. Vestibulum fermentum tortor id m...")';

        $this->assertSame(varToString($lorem256), $lorem256Expected);
    }

    public function testVarToStringObject(): void
    {
        $this->assertSame(varToString(new \DateTime()), 'an object of type DateTime');
    }

    public function testVarToStringArray(): void
    {
        $this->assertSame(varToString(['hi', 'hello']), 'an array ([0 => ..., 1 => ...])');
    }

    public function testVarToStringNumber(): void
    {
        $this->assertSame(varToString(123), 'a number (123)');
    }

}
