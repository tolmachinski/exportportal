<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::cleanOutput
 */
class CleanOutputTest extends TestCase
{
    /**
     * @dataProvider stringsWithSpecialChairsDataProvider
     *
     * @param mixed $input
     * @param mixed $output
     */
    public function testCleanOutputWithSpecialChairs($input, $output): void
    {
        $this->assertEquals(cleanOutput($input), $output);
    }

    /**
     * @dataProvider failedConversions
     *
     * @param mixed $input
     */
    public function testFailWhenInputCannotBeCastedToString($input, string $errorText): void
    {
        $this->expectError();
        $this->expectErrorMessage($errorText);
        \cleanOutput($input);
    }

    /**
     * @dataProvider stringsWithIncorrectDataProvider
     *
     * @param mixed $input
     * @param mixed $output
     */
    public function testCleanOutputFail($input, $output): void
    {
        $this->assertNotEquals(cleanOutput($input), $output);
    }

    public function stringsWithSpecialChairsDataProvider(): array
    {
        // creating of stringable object
        $stringableObject = new class() {
            public function __toString()
            {
                return '';
            }
        };

        return [
            ['<p>hello</p>', '&lt;p&gt;hello&lt;/p&gt;'],
            ['<script>alert(\'123\');</script>', '&lt;script&gt;alert(&#039;123&#039;);&lt;/script&gt;'],
            ['<div class="div">Hello</div>', '&lt;div class=&quot;div&quot;&gt;Hello&lt;/div&gt;'],
            ['😂🎶😢🐱‍🏍', '😂🎶😢🐱‍🏍'],
            ["\x5A\x6F", 'Zo'],
            [1.245, '1.245'],
            [1, '1'],
            [$stringableObject, ''],
            [
                '<img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQYtfZRhbGQtq2BapB2MXJfWIO2QriO5Wx3qQ&usqp=CAU">',
                '&lt;img src=&quot;https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQYtfZRhbGQtq2BapB2MXJfWIO2QriO5Wx3qQ&amp;usqp=CAU&quot;&gt;',
            ],
        ];
    }

    public function stringsWithIncorrectDataProvider(): array
    {
        return [
            [true, 'true'],
            [null, 'null'],
            [curl_init('http://www.example.com/'), 'Resource id #186'],
        ];
    }

    public function failedConversions(): array
    {
        return [
            [['123'], 'Array to string conversion'],
            [new \stdClass(), 'Object of class stdClass could not be converted to string'],
        ];
    }
}
