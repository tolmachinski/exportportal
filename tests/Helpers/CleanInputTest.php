<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;


/**
 * @internal
 * @covers ::cleanInput
 */
class CleanInputTest extends TestCase
{
    /**
     * @dataProvider notTrimmedStringsDataProvider
     *
     * @param mixed $input
     */
    public function testCleanInputTrim($input): void
    {
        $this->assertSame(cleanInput($input), 'hello');
    }

    /**
     * @dataProvider stringsWithTagsDataProvider
     *
     * @param mixed $input
     */
    public function testCleanInputNoTags($input): void
    {
        $this->assertSame(cleanInput($input), 'hello');
    }

    /**
     * @dataProvider scriptsStringsDataProvider
     *
     * @param mixed $input
     */
    public function testCleanInputNoScripts($input): void
    {
        $this->assertEmpty(cleanInput($input), 'hello');
    }

    /**
     */
    public function testCleanInputToLower(): void
    {
        $this->assertSame(cleanInput('HeLLo', true), 'hello');
    }

    /**
     */
    public function testCleanInputWithWhitespaces(): void
    {
        $this->assertSame(cleanInput('how are    you?', true, true), 'how are you?');
    }

    public function notTrimmedStringsDataProvider(): array
    {
        return [
            [' hello'],
            ['hello '],
        ];
    }

    public function stringsWithTagsDataProvider(): array
    {
        return [
            ['<tag>hello</tag>'],
            ['<p>hello</p>'],
            ['hello</p>'],
            ['<p>hello'],
            ['<p>he<string>llo'],
        ];
    }

    public function scriptsStringsDataProvider(): array
    {
        return [
            ['<script>hello</script>'],
            ['<style>hello</style>'],
        ];
    }

}
