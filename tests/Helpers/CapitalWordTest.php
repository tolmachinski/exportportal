<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::capitalWord
 */
class CapitalWordTest extends TestCase
{
    /**
     * @dataProvider rightWordsForCapitalWord
     *
     * @param mixed $word
     */
    public function testCapitalWordRightData($word): void
    {
        $this->assertSame(capitalWord($word), 'Hello and Bye');
    }

    public function testCapitalWordEmptyData(): void
    {
        $this->assertSame(capitalWord(''), '');
    }

    public function rightWordsForCapitalWord(): array
    {
        return [
            ['Hello and Bye'],
            ['hello and bye'],
            ['HELLO AND BYE'],
            ['HeLLo And ByE'],
        ];
    }
}
