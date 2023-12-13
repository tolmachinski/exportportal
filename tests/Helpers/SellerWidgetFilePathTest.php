<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers ::sellerWidgetFilePath
 */
class SellerWidgetFilePathTest extends TestCase
{
    /**
     * @dataProvider urlData
     *
     * @param mixed $id
     * @param mixed $key
     * @param mixed $url
     * @param mixed $output
     */
    public function testSellerWidgetFilePathWithURLs($id, $key, $url, $output): void
    {
        $this->assertSame(sellerWidgetFilePath($id, $key, $url), $output);
    }

    /**
     * @dataProvider failedConversions
     *
     * @param mixed $id
     * @param mixed $key
     * @param mixed $url
     */
    public function testFailedConversions($id, $key, $url, string $errorMessage): void
    {
        $this->expectError();
        $this->expectErrorMessage($errorMessage);
        sellerWidgetFilePath($id, $key, $url);
    }

    public function urlData(): iterable
    {
        yield [1, 'test', 'http://youtube.com', 'public/widgets/1/test.youtube.com.widget'];
        yield [1, 'test', 'https://youtube.com', 'public/widgets/1/test.youtube.com.widget'];
        yield [1, 'test', 'https://youtube.com/hello', 'public/widgets/1/test.youtube.com.widget'];
        yield [1, 'test', 'https://youtube.com/hello/userdfsf?dfsdf=dfsd', 'public/widgets/1/test.youtube.com.widget'];
        yield [01, 123, 'www.google.com/search?q=OWASP%20ZAP', 'public/widgets/1/123.google.com.widget'];
        yield ['010', 'DFDD', 'www.google.com.eu/search?q=OWASP%20ZAP', 'public/widgets/010/DFDD.google.com.eu.widget'];
        yield [10, 'test', 'http://29249708982385fff20586.owasp.org', 'public/widgets/10/test.29249708982385fff20586.owasp.org.widget'];
        yield [null, 'test', 'http://http://yes.com', 'public/widgets//test.http:.widget'];
        yield [null, null, 'http://http://yes.com', 'public/widgets//.http:.widget'];
        yield [null, null, null, 'public/widgets//..widget'];
        yield [true, true, false, 'public/widgets/1/1..widget'];
    }

    public function failedConversions(): iterable
    {
        yield [[], [], [], 'Array to string conversion'];
    }
}
